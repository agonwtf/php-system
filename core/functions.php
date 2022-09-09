<?php
session_start();
if (empty($_SESSION['language']) || isset($_POST['en'])) {
    $_SESSION['language'] = 'en';
} else if (isset($_POST['al'])) {
    $_SESSION['language'] = 'al';
}

require_once 'db.php';
$db = new DB;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function pr($arr)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

function setTitle()
{
    $siteName = 'System - ';
    $path = explode('/', $_SERVER['PHP_SELF']);
    $title = str_replace('_', '', end($path));
    if (in_array(end($path), ['index', 'index.php'])) $title = 'Home';
    return $siteName . ucfirst($title);
}

function getAttribute($attribute)
{
    global $db;
    $attribute = $db->from('attributes')->where('attribute_name', $attribute)->where('language', $_SESSION['language'])->first();
    if (!empty($attribute)) return $attribute['attribute_value'];
}

function isEmpty($params)
{
    foreach ($params as $param) {
        if (empty($_POST[$param])) return false;
    }
    return true;
}
