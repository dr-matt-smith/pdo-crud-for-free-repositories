<?php

require_once __DIR__ . '/../vendor/autoload.php';

// constants for our DB configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'fred');
define('DB_PASS', 'smith');
define('DB_NAME', 'evote');

use Evote\DvdRepository;
use Evote\Dvd;

$dvdRepository = new DvdRepository();
$dvds = $dvdRepository->getAll();

var_dump($dvds);