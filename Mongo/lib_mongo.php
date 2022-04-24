<?php
spl_autoload_register(function ($class) {
  require $_SERVER['DOCUMENT_ROOT'] . '/Mongo/' .str_replace('\\', '/', $class) . '.php';
});

require $_SERVER['DOCUMENT_ROOT'] . '/Mongo/MongoDB/functions.php';

function mongo_connect() {
    $client = new MongoDB\Client("mongodb://phpUserName:SuperSecretPassword2@localhost");

    try {
        $client -> listDatabases();
    } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
        http_response_code(500);
        echo json_encode(array('status' => 'server error', 'message' => 'The database server is not available.'), JSON_PRETTY_PRINT);
        exit(1);
    }

    return $client -> {'streamingserver'};
}

function mongo_find($collection, $filter = null, $options = null) {
    if (is_null($filter) || $filter === '') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    if (is_null($options) || $options === '') {
        $options = array();
    } else if (is_string($options)) {
        $options = json_decode($options, true);
    }
    $coll = mongo_connect() -> $collection;
    $result = $coll -> find($filter, $options);

    return iterator_to_array($result);
}

function mongo_distinct($collection, $field, $filter = null, $options = null) {
    if (is_null($filter) || $filter === '') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    if (is_null($options) || $options === '') {
        $options = array();
    } else if (is_string($options)) {
        $options = json_decode($options, true);
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> distinct($field, $filter, $options);

    return $result;
}

function mongo_count($collection, $filter = null, $options = null) {
    if (is_null($filter) || $filter === '') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    if (is_null($options) || $options === '') {
        $options = array();
    } else if (is_string($options)) {
        $options = json_decode($options, true);
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> count($filter, $options);

    return $result;
}

function mongo_insert_one($collection, $document) {
    if (is_string($document)) {
        $document = json_decode($document, true);
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> insertOne($document);
    return $result -> getInsertedId();
}

function mongo_insert_many($collection, $documents) {
    $coll = mongo_connect() -> $collection;
    $result = $coll -> insertMany($documents);
    return $result->getInsertedIds();
}

function mongo_delete_one($collection, $filter) {
    if (empty($filter) || $filter === '{}') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> deleteOne($filter);

    if ($result -> isAcknowledged()) {
        return $result -> getDeletedCount();
    } else {
        return false;
    }
}

function mongo_delete_many($collection, $filter) {
    if (empty($filter) || $filter === '{}') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> deleteMany($filter);

    if ($result -> isAcknowledged()) {
        return $result -> getDeletedCount();
    } else {
        return false;
    }
}

function mongo_update_one($collection, $filter, $update, $options = null) {
    if (is_null($filter) || $filter === '') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    if (is_null($update) || $update === '') {
        return;
    } else if (is_string($update)) {
        $update = json_decode($update, true);
    }

    if (is_null($options)) {
        $options = array();
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> updateOne($filter, $update, $options);
    return $result -> getModifiedCount();
}

function mongo_update_many($collection, $filter, $update, $options = null) {
    if (is_null($filter) || $filter === '') {
        $filter = array();
    } else if (is_string($filter)) {
        $filter = json_decode($filter, true);
    }

    if (is_null($update) || $update === '') {
        return;
    } else if (is_string($update)) {
        $update = json_decode($update, true);
    }

    if (is_null($options)) {
        $options = array();
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> updateMany($filter, $update, $options);
    
    if ($result -> isAcknowledged()) {
        return sprintf("Matched %s document(s). Modified %s document(s).", $result -> getMatchedCount(), $result -> getModifiedCount());
    } else {
        return false;
    }
}

function mongo_bulk_write($collection, $operations, $options = null) {
    if (is_null($options)) {
        $options = array();
    }

    $coll = mongo_connect() -> $collection;
    $result = $coll -> bulkWrite($operations, $options);

    if ($result -> isAcknowledged()) {
        return $result -> getModifiedCount();
    } else {
        return false;
    }
}

// Syntax:
// $joins is an array with one entry for each join
// The key is the field name in the base collection
// The value has format [collection].[field_name]
// For example: array('_modem_id' => 'modem._id') to
// join the modem collection on base._modem_id = modem._id
function mongo_join($collection, $filter = null, $projection = null, $joins = array(), $options = null, $post_sort = false) {
    $dataset = mongo_find($collection, $filter, $options);

    foreach ($joins as $key => $value) {
        $base_field = $key;
        $join_collection = explode('.', $value, 2)[0];
        $join_field = explode('.', $value, 2)[1];

        for ($i = 0; $i < count($dataset); $i++) {
            if (isset($dataset[$i][$base_field]) && !empty($dataset[$i][$base_field])) {
                $join_data = mongo_find($join_collection, array($join_field => $dataset[$i][$base_field]));

                // do not join anything if there was no match or multiple matches
                if (count($join_data) != 1 ) {
                    continue;
                }

                foreach ($join_data[0] as $key => $value) {
                    // do not add the join_field to the dataset
                    if ($key == $join_field) {
                        continue;
                    }

                    // do not set empty strings, but set empty arrays and zero values
                    if ($value !== "") {
                        $dataset[$i][$join_collection . '_' . $key] = $value;
                    }
                }
            }
        }
    }
    
    $dataset = mongo_apply_projection($dataset, $projection);

    if ($post_sort && !empty($options['sort'])) {
        mongo_sort($dataset, $options['sort']);
    }

    return $dataset;
}

function mongo_apply_projection($dataset, $projection) {
    if (is_object($dataset)) {
        $dataset = iterator_to_array($dataset);
    }

    if (empty($projection)) {
        return $dataset;
    }

    for ($i = 0; $i < count($dataset); $i++) {
        if (is_object($dataset[$i])) {
            $dataset[$i] = iterator_to_array($dataset[$i]);
        }

        $new_row = array();

        foreach ($dataset[$i] as $key => $value) {
            if (isset($projection[$key]) && !empty($projection[$key])) {
                $new_row[$key] = $value;
            } else if ($key == '_id') {
                $new_row[$key] = $value;
            }
        }

        $dataset[$i] = $new_row;
    }

    return $dataset;
}

function mongo_sort(&$dataset, $sort) {
    global $sort_column;
    global $sort_direction;

    $sort_column = array_keys($sort)[0];
    $sort_direction = $sort[$sort_column];

    usort($dataset, function ($a, $b) {
        global $sort_column;
        global $sort_direction;

        return (($a[$sort_column] ?? '') <=> ($b[$sort_column] ?? '')) * $sort_direction;
    });
}

function mongo_to_object_ids($array) {
    for ($i = 0; $i < count($array); $i++) {
        $array[$i] = new MongoDB\BSON\ObjectId($array[$i]);
    }

    return $array;
}

function mongo_collection_list() {
    return mongo_connect() -> listCollections();
}

function mongo_get_collection_names() {
    foreach (mongo_collection_list() as $collection) {
        $collections[] = $collection->getName();
    }
    sort($collections);
    return $collections;
}
 ?>
