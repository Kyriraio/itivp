<?php
namespace api;

require '../vendor/autoload.php';
require '../lib/Api/ApiHandler.php';

$apiHandler = new ApiHandler();
$apiHandler->handleRequest();