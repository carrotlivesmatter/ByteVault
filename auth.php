<?php
session_start();
include 'hfapi.class.php';
$bytes = new bytes();

$a = $bytes->finishAuth();

if (!$a) {
    echo "Unable to obtain access token! Errors Below.<br /><br />";
    foreach ($bytes->getErrors() as $error) {
        echo "{$error}<br />";
    }
    exit;
} else {
    $access_token = $bytes->getAccessToken();
    $_SESSION['access_token'] = $access_token;
    $uid = $bytes->getUID();
    $my = $bytes->read([
        "me" => [
            "uid" => true,
            "username" => true,
            "avatar" => true,
            "usergroup" => true,
            "bytes" => true,
        ]
    ]);
    if (!$my) {
        echo "Unable to read from API! Errors Below.<br /><br />";
        foreach ($this->bytes->getErrors() as $error) {
            echo "{$error}<br />";
        }
        exit;
    }
    $_SESSION['data'] = $my;
    $_SESSION['logged_in'] = 1;
    if ($bytes->checkAccessToken()) {
        header('Location: index.php');
    }
}
?>