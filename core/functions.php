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

function setTitle(string $siteName = 'Agon'): void
{
    $path = $_SERVER['PHP_SELF'];
    $path = explode('/', $path);
    $title = str_replace(['-', '_', '.php'], ' ', end($path));
    echo $siteName . ' - ' . ucfirst($title);
}

function notEmpty(array $params): bool
{
    foreach ($params as $param) {
        if (empty($_POST[$param])) {
            return false;
            header("Refresh:0");
        }
    }
    return true;
}

function isValid(array $inputs): bool
{
    foreach ($inputs as $input) {
        switch ($input) {
            case 'accountType':
                if (!in_array($_POST[$input], ['personal', 'business'])) {
                    return false;
                    header("Refresh:0");
                }
                break;
            case 'fullName':
                if (ctype_alpha(str_replace(' ', '', $input)) === false) {
                    return false;
                    header("Refresh:0");
                } else if (strlen($_POST[$input]) > 30) {
                    return false;
                    header("Refresh:0");
                } else if (strlen($_POST[$input]) < 5) {
                    return false;
                    header("Refresh:0");
                }
                break;
            case 'email':
                if (!filter_var($_POST[$input], FILTER_VALIDATE_EMAIL)) {
                    return false;
                    header("Refresh:0");
                }
                break;
            case 'phone':
                global $db;
                $phone = $db->from('users')->select('phone')->where('phone', $_POST[$input])->all();
                if (preg_match("/^\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{3})/", $_POST[$input]) || strlen($_POST[$input]) != 9 || count($phone) >= 3) {
                    return false;
                    header("Refresh:0");
                }
                break;
            case 'password':
                if (!strlen($_POST[$input]) >= 8) {
                    return false;
                    header("Refresh:0");
                }
                break;
        }
    }
    return true;
}

function notUsed(array $params): bool
{
    global $db;
    foreach ($params as $param) {
        $data = $db->from('users')->select($param)->where($param, $_POST[$param])->first();
        if (!empty($data)) {
            return false;
            header("Refresh:0");
        }
    }
    return true;
}

function userToken(int $size): string
{
    $bytes = random_bytes($size);
    $token = bin2hex($bytes);
    return $token;
}

function clientIp()
{
    $ip = @unserialize(file_get_contents('http://ip-api.com/php/'));
    if ($ip && $ip['status'] == 'success') {
        return json_encode($ip);
    }
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

function table($arguments)
{
    global $db;
    $rows = $db->from($arguments['tableName'])->select($arguments['columns'])->all();
    $table = "<table class='rounede bg-gray-600'>";
    $table .= "<thead>";
    $table .= "<tr class='text-left whitespace-nowrap'>";
    foreach ($rows[0] as $key => $value) {
        $table .= "<th class='p-3'>";
        $table .= strtoupper(str_replace('_', ' ', $key));
        $table .= "</th>";
    }
    $table .= "</tr>";
    $table .= "</thead>";
    $table .= "<tbody>";
    foreach ($rows as $row) {
        $table .= "<tr class='odd:bg-gray-700'>";
        foreach ($row as $data) {
            if (isset($arguments['unset'])) {
                if (is_array($arguments['unset'])) {
                    foreach ($arguments['unset'] as $key => $value) {
                        unset($row[$value]);
                    }
                } else {
                    unset($row[$arguments['unset']]);
                }
            }
            $table .= "<td class='p-3'>";
            $table .= $data;
            $table .= "</td>";
        }
        $table .= "</tr>";
    }
    $table .= "</tbody>";
    echo $table .= "</table>";
}


function pwdHash(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function pwdVerify(string $email, string $password): array
{
    global $db;
    $hash = $db->from('users')->select('password')->where('email', $email)->first();
    if (!empty($hash)) {
        if (password_verify($password, $hash['password'])) {
            return [
                'status' => true,
            ];
        }
    }
    return [
        'status' => false,
        'message' => 'This email-password combination does not exist.',
    ];
}

function sessLogin(): void
{
    global $db;
    $user = $db->from('users')->select('uid')->where('email', $_POST['email'])->first();
    $_SESSION['login'] = [
        'uid' => $user['uid'],
    ];
    $db->update('users')->set([
        'status' => 'online',
    ]);
}

if (isset($_POST['logout'])) {
    unset($_SESSION['login']);
    global $db;
    $db->update('users')->set([
        'status' => 'offline',
    ]);
    header('Location: login.php');
}

function getUser()
{
    if (isset($_SESSION['login'])) {
        global $db;
        $user = $db->from('users')->where('uid', $_SESSION['login']['uid'])->first();
        if (!empty($user)) {
            unset($user['password']);
            return $user;
        }
    }
}

$user = getUser();

function getAttr($attrName)
{
    global $db;
    $attr = $db->from('attributes')->where('language', $_SESSION['lang'])->where('attr_name', $attrName)->first();
    return !empty($attr) ? $attr : 'Please fill in this gap';
}

function verifyMail()
{
    global $db;
    $code = userToken(3);
    $db->insert('pendingUsers')->set([
        'email' => $_POST['email'],
        'code' => $code,
        'expires' => time() + (86400 * 7),
    ]);
}

function errMessage($message = '', $type)
{
    if (!empty($message)) {
        switch ($type) {
            case 'error':
                echo "<div class='absolute w-[80%] top-5 flex p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800' role='alert'>
                            <svg aria-hidden='true' class='flex-shrink-0 inline w-5 h-5 mr-3' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'>
                                <path fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z' clip-rule='evenodd'></path>
                            </svg>
                            <span class='sr-only'>WARNING</span>
                            <div>
                                <span class='font-medium'>Danger alert!</span>
                                {$message}
                            </div>
                        </div>";
                break;
            case 'warning':
                echo "<div class='absolute w-[80%] top-5 flex p-4 mb-4 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-200 dark:text-yellow-800' role='alert'>
                            <svg aria-hidden='true' class='flex-shrink-0 inline w-5 h-5 mr-3' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'><path fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z' clip-rule='evenodd'></path></svg>
                            <span class='sr-only'>Info</span>
                            <div>
                                <span class='font-medium'>Warning alert!</span>
                                {$message}
                            </div>
                        </div>";
                break;
            default:
                echo "<div class='flex p-4 mb-4 text-sm text-blue-700 bg-blue-100 rounded-lg dark:bg-yellow-200 dark:text-yellow-800' role='alert'>
                            <svg aria-hidden='true' class='flex-shrink-0 inline w-5 h-5 mr-3' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'><path fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z' clip-rule='evenodd'></path></svg>
                            <span class='sr-only'>Info</span>
                            <div>
                                <span class='font-medium'>Warning alert!</span>
                                {$message}
                            </div>
                        </div>";
                break;
        }
    }
}

$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
if (isset($_SESSION['login'])) {
    if ($scriptName[2] == 'admin') {
        if (!in_array($user['role'], ['admin', 'root'])) {
            header('Location: ../index.php');
            die();
        }
    } else if (in_array(end($scriptName), ['login.php', 'register.php'])) {
        header('Location: index.php');
        die();
    }
} else {
    if (!in_array(end($scriptName), ['login.php', 'login', 'register.php', 'register'])) {
        if ($scriptName[2] == 'admin') {
            header('Location: ../login.php');
            die();
        } else {
            header('Location: login.php');
            die();
        }
    }
}
