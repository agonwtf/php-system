<?php
include_once 'partials/header.php';
if (isset($_SESSION['login'])) {
    header("Location: index.php");
}
?>

<form action="" method="post">
    <input type="email" name="email" id="">
    <br>
    <input type="password" name="password" id="">
    <br>
    <button type="submit" name="login" id="">Login</button>
</form>

<?php include_once 'partials/header.php'; ?>