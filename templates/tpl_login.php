<?php if (!empty($error)) : ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>
Login/Pass: test/test<br>
<form id="frm_Login" action="index.php" method="post">
    <input<?php echo (!empty($locked) ? ' disabled="disabled"' : ''); ?> type="text" name="username" placeholder="<?php echo Core::getTranslation('benutzername'); ?>" value="<?php echo Request::getInstance()->get('username'); ?>" tabindex="1" autofocus /><br>
    <input<?php echo (!empty($locked) ? ' disabled="disabled"' : ''); ?> type="password" name="password" placeholder="<?php echo Core::getTranslation('passwort'); ?>" tabindex="2" /><br>
	<button<?php echo (!empty($locked) ? ' disabled="disabled"' : ''); ?> type="submit" title="<?php echo Core::getTranslation('anmelden'); ?>" name="btn_Login" tabindex="3"><?php echo Core::getTranslation('anmelden'); ?></button>
</form>