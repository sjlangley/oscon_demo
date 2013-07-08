<?php

require_once 'google_api_php_client/src/Google_Client.php';
require_once 'google_api_php_client/src/contrib/Google_DatastoreService.php';

// Set your client id, service account name, and the path to your private key.
// For more information about obtaining these keys, visit:
// https://developers.google.com/console/help/#service_accounts
const CLIENT_ID = 'INSERT_YOUR_CLIENT_ID';
const SERVICE_ACCOUNT_NAME = 'INSERT_YOUR_SERVICE_ACCOUNT_NAME';

// Make sure you keep your key.p12 file in a secure location, and isn't
// readable by others.
const KEY_FILE = '/super/secret/path/to/key.p12';

$client = new Google_Client();
$client->setApplicationName('OSCON App Engine Demo');

session_start();

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
}

// Load the key in PKCS 12 format (you need to download this from the
// Google API Console when the service account was created.
$key = file_get_contents(KEY_FILE);
$client->setAssertionCredentials(new Google_AssertionCredentials(
        SERVICE_ACCOUNT_NAME,
        array('https://www.googleapis.com/auth/prediction'),
        $key)
);

$client->setClientId(CLIENT_ID);
$datastore = new Google_DatastoreService($client);

$req = new Google_BlindWriteRequest();
$entity = $req->getMutation()->getUpsert()-

