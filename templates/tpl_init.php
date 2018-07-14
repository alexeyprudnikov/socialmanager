<?php if (!empty($success)):
    echo $success.'<br><br>';
?>
zur Startseite<br>
<a href="index.php">Fortfahren</a>

<?php else: ?>

<?php if (!empty($error)) {
    echo $error.'<br><br>';
}
?>
db init<br>
<a href="?dbinit=1">Init database</a>
<?php endif; ?>