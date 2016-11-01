<?php

defined('IS_DEVELOPMENT') OR exit('No direct script access allowed');

require 'mongodb_helper.php';

$json = json_decode($input);

$data['facebook_id'] = isset($json->facebook_id) ? $json->facebook_id : "";
$data['value1'] = (isset($json->value1) && $json->value1 >= 0) ? $json->value1 : 0;
$data['value2'] = (isset($json->value2) && $json->value2 >= 0) ? $json->value2 : 0;

if (trim($data['facebook_id']) == "") {
    return array(
        "status" => FALSE,
        "affected_row" => 0,
        "message" => "Error: facebook_id is empty"
    );
}

$db = get_mongodb(IS_DEVELOPMENT);

$document = $db->User->findOne([ 'facebook_id' => $data['facebook_id']]);

$affected_row = 0;
if (is_object($document)) {
    if (($data['value2'] > $document->value2) || ($data['value1'] > $document->value1) || (isset($json->overwrite) && $json->overwrite == true)) {
        $data['updated_date'] = date('Y-m-d H:i:s');
        $db->User->updateOne(['_id' => bson_oid((string) $document->_id)], ['$set' => $data]);
        $affected_row = 1;
    }
    return array("status" => TRUE, "affected_row" => $affected_row);
} else {
    $data['created_date'] = date('Y-m-d H:i:s');
    $db->User->insertOne($data);
    return array("status" => FALSE, "affected_row" => $affected_row, "message" => "User not found"); 
}

//echo json_encode(array("status" => TRUE));

