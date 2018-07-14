<!DOCTYPE html>
<html>
	<head>
		<base href="<?php echo $Base; ?>" />
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="alexeyprudnikov">
        <link type="text/css" href="<?php $this->printPublicIncludePath(); ?>lib/bootstrap/css/bootstrap.min.css?<?php $this->printHash(); ?>" rel="stylesheet" />
        <link type="text/css" href="<?php $this->printPublicIncludePath(); ?>lib/font-awesome/css/font-awesome.min.css?<?php $this->printHash(); ?>" rel="stylesheet" />
		<link type="text/css" href="<?php $this->printPublicIncludePath(); ?>lib/jqueryui/jquery-ui.min.css?<?php $this->printHash(); ?>" rel="stylesheet" />
        <link type="text/css" href="<?php $this->printPublicIncludePath(); ?>lib/fresco/css/fresco/fresco.css?<?php $this->printHash(); ?>" rel="stylesheet" />

        <link type="text/css" href="<?php $this->printPublicIncludePath(); ?>css/common.css?<?php $this->printHash(); ?>" rel="stylesheet" />
		
		<script type="text/javascript" src="<?php $this->printPublicIncludePath(); ?>lib/jquery/jquery.min.js?<?php $this->printHash(); ?>"></script>
		<script type="text/javascript" src="<?php $this->printPublicIncludePath(); ?>lib/jqueryui/jquery-ui.min.js?<?php $this->printHash(); ?>"></script>
        <script type="text/javascript" src="<?php $this->printPublicIncludePath(); ?>lib/bootstrap/js/bootstrap.min.js?<?php $this->printHash(); ?>"></script>
        <script type="text/javascript" src="<?php $this->printPublicIncludePath(); ?>lib/jquery-lazyload/jquery.lazyload.js?<?php $this->printHash(); ?>"></script>
        <script type="text/javascript" src="<?php $this->printPublicIncludePath(); ?>lib/fresco/js/fresco/fresco.js?<?php $this->printHash(); ?>"></script>
		
		<script type="text/javascript" src="<?php $this->printPublicIncludePath(); ?>js/core.js?<?php $this->printHash(); ?>"></script>
		
		<title><?php echo Core::getTranslation('socialmedia_manager'); ?></title>
	</head>
	<body>
		<div>
			<div>
				<a href="index.php"><img src="<?php $this->printPublicIncludePath(); ?>img/logo.png" alt ="<?php echo Core::getTranslation('socialmedia_manager'); ?>" /></a>
                <?php if($User->Id): ?>
                    <?php echo (!$User->FirstName && !$User->FirstName) ? $User->UserName : $User->FirstName." ".$User->LastName;?>
                     | <a href="index.php?logout=1"><?php echo Core::getTranslation('abmelden'); ?></a>
				<?php endif; ?>
			</div>
            <div>