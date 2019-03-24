<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Tudublin\MovieRepository;

// for Car DB actions
$movieRepository = new MovieRepository();

//$movieRepository->delete(2);
//$movies = $movieRepository->searchByColumn('title', 'jaws');
//
//
//$movie = $movieRepository->getOneById(3);
//$movie->setTitle('lskdjflksdjflds');
//$movieRepository->update($movie);

$m = new \Tudublin\Movie();
$m->setTitle('pop');
$m->setPrice(8.01);
$movieRepository->create($m);

$movies = $movieRepository->getAll();

var_dump($movies);

