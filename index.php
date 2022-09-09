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

<?= getAttribute('main_banner'); ?>

<?php include_once 'partials/header.php'; ?>