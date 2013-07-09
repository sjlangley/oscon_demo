<?php

require_once 'google_api_php_client/src/Google_Client.php';
require_once 'google_api_php_client/src/auth/Google_AssertionCredentials.php';
require_once 'google_api_php_client/src/contrib/Google_DatastoreService.php';

class Datastore {
  static $scopes = [
    "https://www.googleapis.com/auth/datastore",
    "https://www.googleapis.com/auth/userinfo.email",
  ];

  const DATASET_ID = 's~oscon-demo';

  public static function getDataset($config) {
    $client = new Google_Client();
    $client->setApplicationName($config['application-id']);
    $client->setClientId($config['client-id']);
    $client->setAssertionCredentials(new Google_AssertionCredentials(
        $config['service-account-name'],
        self::$scopes,
        $config['private-key']));
    $service = new Google_DatastoreService($client);
    return $service->datasets;
  }
}