<?php

require_once 'config.php';
require_once 'Datastore/Datastore.php';

$dataset = Datastore::getDataset($google_api_config);

$request = new Google_BlindWriteRequest();
$path = new Google_KeyPathElement();
$path->setKind('User');

$propertyMap = [];

$textProperty = new Google_Property();
$value = new Google_Value();
$value->setStringValue('foo');
$textProperty->setValues([$value]);
$propertyMap['text'] = $textProperty;

$key = new Google_Key();
$key->setPath([$path]);

$entity = new Google_Entity();
$entity->setKey($key);
$entity->setProperties($propertyMap);

$mutation = new Google_Mutation();
$mutation->setInsertAutoId([$entity]);
$request->setMutation($mutation);


$resp = $dataset->blindWrite(Datastore::DATASET_ID, $request);

var_dump($resp);
//$request = new Google_LookupRequest();
//$request->setKeys([]);
//$response = $dataset->lookup(Datastore::DATASET_ID, $request);



