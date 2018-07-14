<?php
/**
 * SocialMedia Manager
 * @author: alexeyprudnikov
 */
#error_reporting(E_ALL);
#ini_set("display_errors", 1);

require_once('core/includes/config.php');
include("core/controllers/autoloader.php");

$Core = Core::getInstance();
$Core->start();
