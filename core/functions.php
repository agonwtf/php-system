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
    $title = str_replace(['_', '-', '.php'], ' ', end($path));
    if (in_array(end($path), ['index', 'index.php'])) $title = 'Home';
    return $siteName . ucfirst($title);
}

function getAttribute($attribute)
{
    global $db;
    $attribute = $db->from('attributes')->where('attribute_name', $attribute)->where('language', $_SESSION['language'])->first();
    if (!empty($attribute)) return $attribute['attribute_value'];
}

function isRequired($params)
{
    foreach ($params as $param) {
        if (empty($_POST[$param])) return false;
    }
    return true;
}

function generateToken()
{
    return bin2hex(openssl_random_pseudo_bytes(15));
}

function pwdHash($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function sessionSet($email)
{
    global $db;
    $userToken = $db->from('users')->select('u_id')->where('email', $email)->first();
    $_SESSION['login'] = [
        'u_id' => $userToken['u_id'],
    ];
}

function getUser()
{
    if (isset($_SESSION['login'])) {
        global $db;
        $user = $db->from('users')->where('u_id', $_SESSION['login']['u_id'])->first();
        unset($user['password'], $user['token']);
        return $user;
    }
}

$user = getUser();

if (isset($_POST['sign-up'])) {
    if (isRequired(['email', 'password'])) {
        global $db;
        $emailExists = $db->from('users')->where('email', $_POST['email'])->first();
        if (empty($emailExists)) {
            $db->insert('users')->set([
                'email' => $_POST['email'],
                'password' => pwdHash($_POST['password']),
            ]);
            sessionSet($_POST['email']);
            header('Location: index.php');
        } else {
            echo 'An account with this email address already exists!';
        }
    } else {
        echo 'Please fill out the blanks!';
    }
}

if (isset($_POST['login'])) {
    if (isRequired(['email', 'password'])) {
        global $db;
        $hash = $db->from('users')->select('password')->where('email', $_POST['email'])->first();
        if (!empty($hash)) {
            if (password_verify($_POST['password'], $hash['password'])) {
                sessionSet($_POST['email']);
                header('Location: index.php');
            } else {
                echo 'Your email or password is incorrect!';
            }
        } else {
            echo 'An account with this email does not exist!';
        }
    } else {
        echo 'Please fill out the blanks!';
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
}
