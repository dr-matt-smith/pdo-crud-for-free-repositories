<?php
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../src/evote/Dvd.php';
require_once __DIR__ . '/../src/evote/DvdRepository.php';

// constants for our DB configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'fred');
define('DB_PASS', 'smith');
define('DB_NAME', 'evote');

use Evote\DvdRepository;
use Evote\Dvd;

$dvdRepository = new DvdRepository();
$dvds = $dvdRepository->getAll();

foreach($dvds as $dvd){
    /**
     * @var $dvd Dvd
     */
    print PHP_EOL .  'id = ' . $dvd->getId();
    print PHP_EOL .  '<br>';
    print PHP_EOL .  'title = ' . $dvd->getTitle();
    print PHP_EOL .  '<br>';
    print PHP_EOL .  'category = ' . $dvd->getCategory();
    print PHP_EOL .  '<p>';

}

print '<hr>';

$dvdsSearch = $dvdRepository->searchByTitleOrCategory('man');


foreach($dvdsSearch as $dvd){
    /**
     * @var $dvd Dvd
     */
    print PHP_EOL .  'id = ' . $dvd->getId();
    print PHP_EOL .  '<br>';
    print PHP_EOL .  'title = ' . $dvd->getTitle();
    print PHP_EOL .  '<br>';
    print PHP_EOL .  'category = ' . $dvd->getCategory();
    print PHP_EOL .  '<p>';

}