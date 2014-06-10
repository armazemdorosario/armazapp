<?php
$loader = require 'vendor/autoload.php';
use armazemapp\FacebookAdapter;
$facebook = new FacebookAdapter();
$facebook->logout();