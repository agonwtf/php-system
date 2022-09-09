<?php
include_once 'partials/header.php';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
}
?>

<form action="" method="post">
    <button type="submit" name="en">EN</button>
    <button type="submit" name="al">AL</button>
    <button type="submit" name="logout" id="">Logout</button>
</form>

<?php
echo getAttribute('main_banner');
table(['tableName' => 'users', 'columns' => '*', 'unset' => 'password']);
?>

<?php include_once 'partials/header.php'; ?>