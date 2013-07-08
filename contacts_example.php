<?php
/**
 * Created by JetBrains PhpStorm.
 * User: slangley
 * Date: 7/7/13
 * Time: 8:26 PM
 * To change this template use File | Settings | File Templates.
 */


require_once 'google_api_php_client/src/Google_Client.php';
require_once __DIR__ . '/' .  'config.php';

session_start();

$client = new Google_Client();
$client->setApplicationName('PHP on App Engine OSCON demo');
$client->setScopes("http://www.google.com/m8/feeds/");

$client->setClientId($google_api_config['client-id']);
$client->setClientSecret($google_api_config['client-secret']);
$client->setRedirectUri($google_api_config['redirect-url']);
$client->setDeveloperKey($google_api_config['developer-key']);

if (isset($_GET['code'])) {
    $client->authenticate();
    $_SESSION['token'] = $client->getAccessToken();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['token']);
    $client->revokeToken();
}

if ($client->getAccessToken()) {
    $req = new Google_HttpRequest("https://www.google.com/m8/feeds/contacts/default/full");
    $val = $client->getIo()->authenticatedRequest($req);

    // The contacts api only returns XML responses.
    $response = json_encode(simplexml_load_string($val->getResponseBody()));
    print "<pre>" . print_r(json_decode($response, true), true) . "</pre>";

    // The access token may have been updated lazily.
    $_SESSION['token'] = $client->getAccessToken();
} else {
    $auth = $client->createAuthUrl();
}

if (isset($auth)) {
    print "<a class=login href='$auth'>Connect Me!</a>";
} else {
    print "<a class=logout href='?logout'>Logout</a>";
}