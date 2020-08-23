# pdo-crud-for-free-repositories+(repositories)

[![Build Status](https://travis-ci.org/dr-matt-smith/pdo-crud-for-free-repositories.svg?branch=master)](https://travis-ci.org/dr-matt-smith/pdo-crud-for-free-repositories) [![Latest Stable Version](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/v/stable)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories) [![Total Downloads](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/downloads)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories) [![Latest Unstable Version](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/v/unstable)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories) [![License](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/license)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories)


Note - this is essentially an alternative approach to the [pdo-crud-for-free](link-packagist) package



This package provides a few classes to try to give programmers using PDO (with MySQL) in a simple way some instance CRUD (create-read-update-delete) methods, 'for free', simply by creating an entity repository sub-class of Mattsmithdev\PdoCrudRepo\DatabaseTableRepository.

All code is (intended :-) to follow PSR-1, PSR-12 coding standards. Classes are following the PSR-4 autoloading standard.

## Example project using this library

There is an example project illustrating use of this library:

- [https://github.com/dr-matt-smith/pdo-crud-for-free-repositories-example-project](https://github.com/dr-matt-smith/pdo-crud-for-free-repositories-example-project)


## Install

Via Composer

``` bash
$ composer require mattsmithdev/pdo-crud-for-free-repositories
```


## Usage

This example assumes you have a MySQL DB table named 'movie', with columns 'id' and 'title'. You need to write a corresponding class 'Movie' (note capitalization on the first letter - since this is a PHP class). Also you need to write a repository class to work between your PHP class and is corresponding table, in this example the repository class is named 'MovieRepository':

``` php
    // file: /src/Movie.php
    namespace <MyNameSpace>;
    
    class Movie
    {
        // private properties with EXACTLY same names as DB table columns
        private $id;
        private $title;
        
        public function getId()
        {
            return $this->id;
        }
        
        public function getTitle()
        {
            return $this->title;
        }
    }
```


``` php
    // file: /src/MovieRepository.php
    namespace <MyNameSpace>;
    
    use Mattsmithdev\PdoCrudRepo\DatabaseTableRepository;
    
    class MovieRepository extends DatabaseTableRepository
    {
        // no methods needed if you've followed defaults
        // all the 'magic' is done through relfection ...
    }

```


``` php
    // file: /public-web/index.php or /src/SomeController->method()
    
    require_once __DIR__ . '/<PATH_TO_AUTLOAD>';
    
    // create a repository object
    use <MyNameSpace>\MovieRepository;
    $movieRepository = new MovieRepository();
    
    // get all records from DB as an array of Dvd objects
    $movies = $movieRepository->findAll();
    
    // output each Dvd object as HTML lines in the form 'title = Jaws II'
    foreach($movies as $movie){
        /**
         * @var $movie <MyNameSpace>\Movie
         */
        print 'id = ' . $movie->getId();
        print '<br>';
        print 'title = ' . $movie->getTitle();
        print '<br>';
    }
```

Finally, you need to have defined your DB connection credentials in a file `.env` as follows:

```dotenv
    MYSQL_USER=root
    MYSQL_PASSWORD=passpass
    MYSQL_HOST=127.0.0.1
    MYSQL_PORT=3306
    MYSQL_DATABASE=evote
```

The named database schema will be created, if it does not already exist...

For more details see below. Also there is a full sample web application project on GitGub at:
 [pdo-crud-for-free-repositories-example-project](https://github.com/dr-matt-smith/pdo-crud-for-free-repositories-example-project)

# More detailed usage instructions (and important assumptions)

## ASSUMPTION 1: lowerCamelCase - DB table column names matching PHP Class properties
This tool assumes your database table column names, and their corresponding PHP private class properties are named consistently in 'lowerCamelCase'
e.g.

    id
    title
    category
    price
    vatRate
    firstName
    aLongVariableNameOfSeveralWords

## ASSUMPTION 2: No constructor for your PHP classes.
due to the nature of PDO populating properties of objects when DB rows are converted into object instances
do not have a constructor for the PHP classes that correspond to your DB tables

so you'd create a new object, and use the objects public 'setter' methods
e.g.

``` php

    $m = new Movie();
    $m->setTitle('Jaws');
    $m->setPrice(9.99);
    etc.
```

## ASSUMPTION 3: Each class has an integer, `id` property
Each Entity class should have an integer `id` property.
This property should be an `AUTO_INCREMENT` primary key in the database table schema, e.g.

```sql
    -- SQL statement to create the table --
    create table if not exists movie (
        id integer primary key AUTO_INCREMENT,
        title text,
        price float
    );
```

NOTE: Please don't name this anything else, not `idMovie` or `movieId` or `ID` etc. - just plain old `id`

## ASSUMPTION 4: DB table name is singular and all lower case
This tool assumes your database table name is singular, all **lower case**. E.g.

- table name: `movie`
    - entity class name: `Movie.php`

- table name: `moviecategory`
    - entity class name: `MovieCategory.php`
    
- table name: `alongtablename`
    - entity class name: `ALongTableName`



## Step 1: Create your DB tables.
e.g. create your tables (with integer 'id' field, primary key, auto-increment)

e.g. SQL table to store Movie data
```sql
    -- SQL statement to create the table --
    create table if not exists movie (
        id int primary key AUTO_INCREMENT,
        title text,
        price float,
        category text
    )
```

NOTE: You may do this through code if you put this SQL into the special constant `CREATE_TABLE_SQL` like this:

```php
    class Movie
    {
        const CREATE_TABLE_SQL =
    <<<HERE
     CREATE TABLE IF NOT EXISTS movie (
         id integer PRIMARY KEY AUTO_INCREMENT,
         title text,
         price float,
         category text
     )
     HERE;
    
        ... rest of class ...
```

See notes about Repository methods `createTable()` and `resetTable()` below ...


## Step 2: Create a corresponding PHP (entity) class
e.g.

``` php
    <?php
    namespace Whatever;
    

    class Movie
    {
        private $id;
        private $title;
        private $category;
        private $price;
        
        // and public getters and setters ...
```
            
## Step 3: Create a repository class mapping your DB table to your PHP entity class (that is a subclass from Mattsmithdev\PdoCrud\DatabaseTableRepository)

e.g. create repository class MovieRepository mapping from table `movie` to PHP class `Evote\Movie`:

``` php

    <?php
    namespace Whatever; // same as for Entity calss
    
    use Mattsmithdev\PdoCrudRepo\DatabaseManager;
    use Mattsmithdev\PdoCrudRepo\DatabaseTableRepository;
    
    class MovieRepository extends DatabaseTableRepository
    {
    }
    
```    

Note - personally I find it handy to add a method to create a new object and insert it into the DB - e.g.:

```php
    <?php
    namespace Tudublin;
    
    
    use Mattsmithdev\PdoCrudRepo\DatabaseTableRepository;
    
    class MovieRepository extends DatabaseTableRepository
    {
        public function createAndInsert($title, $price, $category)
        {
            $m = new Movie();
            $m->setTitle($title);
            $m->setPrice($price);
            $m->setCategory($category);
    
            $this->insert($m);
        }
    }
```

## Step 4: Define your MySQL database credentials in a file `.env`

Define your DB connection credentials in a file `.env` as follows:

```dotenv
    MYSQL_USER=root
    MYSQL_PASSWORD=passpass
    MYSQL_HOST=127.0.0.1
    MYSQL_PORT=3306
    MYSQL_DATABASE=evote
```


## Step 5: Now use the 'magically appearing' DB CRUD methods.

e.g. to get an array of all movie records from table 'movie' just write:

``` php
    $movieRepository = new MovieRepository();
    $movies = $movieRepository->getAll();    
```   

NOTE: Can pass optional params to override defaults when creating Repository class:

- if Repository is in a different namespace, pass in name of namespace:

    ```php
    $params = [
        'namespace' => 'DifferentNameSpace'
    ];
    $repo = new MovieRepository($params);
    ```
    

- class name - if not name before `Repository`
    ```php
    $params = [
        'className' => 'differentClassName'
    ];
    $repo = new MovieRepository($params);
    ```
    
- tablename not lowercase version of entity class for repo:
    ```php
    $params = [
        'tableName' => 'differentTableName'
    ];
    $repo = new MovieRepository($params);
    ```

## ->findAll()
this method returns an array of ALL objects for each row of the corresponding DB table
e.g.

``` php
    // array of Dvd objects, populated from database table 'dvds'
    $movieRepository = new MovieRepository();
    $movies = $movieRepository->find();
```

## ->find($id)
this method returns ONE object of class for the corresponding DB table record with the given 'id'
(returns 'null' if no such record exists with that primary key id)
e.g.

``` php
    // one Movie object (or 'null'), populated by row in database table 'movie' with id=27
    $movieRepository = new MovieRepository();
    $movie = $movieRepository->find(27);
```

## ->delete($id)
this method deletes the record corresponding to the given 'id'
returns true/false depending on success of the deletion
e.g.

``` php
    // delete row in database table 'movie' with id=12
    $movieRepository = new MovieRepository();
    $deleteSuccess = $movieRepository->delete(12);
```

    
## ->deleteAll()
this method deletes ALL records for the associated database table
e.g.

``` php
    // delete all rows in database table 'movie'
    $movieRepository = new MovieRepository();
    $deleteSuccess = $movieRepository->deleteAll();
```
    
## ->insert($movie)
this method adds a new row to the database, based on the contents of the provided object
(any 'id' in this object is ignored, since the table is auto-increment, so it's left to the DB to assign a new, unique 'id' for new records)
returns the 'id' of the new record (or -1 if error when inserting)
e.g.

``` php

    // create new object
    $movie = new Movie();
    $movie->setTitle('Jaws II');
    $movie->setCategory('thriller');
    $movie->setPrice(9.99);
    
    // attempt to inset row in database table 'movie' - auto assign new unique `id`
    $movieRepository = new MovieRepository();
    $id = $movieRepository->insert($movie);
    
    // decision based on success/failure of insert
    if ($id < 0){
        // error action
    } else {
        // success action
    }
```    
    
## ->update($movie)
This method adds a UPDATES an existing row in the database, based on the contents of the provided object
returns true/false depending on success of the deletion

e.g.

``` php
    // update DB record for object 'movie'
    $movieRepository = new MovieRepository();
    $updateSuccess = $movieRepository->update($movie);
```    
            
## ->searchByColumn($columnName, $searchText))
Perform an SQL '%' wildcard search on the given column with the given search text
returns an array of objects that match an SQL 'LIKE' query 

e.g.

``` php
    // get all Movies with 'jaws' in the title
    $movieRepository = new MovieRepository();
    $jawsMovies = $movieRepository->searchByColumn('title', 'jaws');
```

## ->dropTable()
Deletes the associated database table

e.g.

``` php
    // drop table `movie` from DB
    $movieRepository = new MovieRepository();
    $movieRepository->dropTable();
```


## ->createTable()
If no SQL parameter is provided, then the code looks for a constant CREATE_TABLE_SQL in the associated entity class, and will execute that SQL

e.g. In class Movie() there is constant:

```php

class Movie
{
    const CREATE_TABLE_SQL =
<<<HERE
 CREATE TABLE IF NOT EXISTS movie (
     id integer PRIMARY KEY AUTO_INCREMENT,
     title text,
     price float,
     category text
 )
 HERE;

    ... rest of class ...
```


## ->createTable($sql)
As above, but the SQL to create the table can be provided as a string parameter to the method

## ->resetTable( $sql = null  )

This runs the sequence drop / create / delete all:

```php
    $this->dropTable();
    $this->createTable($sql); // pass through any SQL provided
    $this->deleteAll();
```

any SQL provided as a parameter is passed threough to `createTable(...)`.


Then in our migration code (for example) we can drop the old table and create a new one as follows:

``` php
    $movieRepository = new MovieRepository();
    $movieRespository->resetTable();
```


## custom PDO methods
If the 'free' DB methods are insufficient, it's easy to add your own methods to your PHP classes that correspond to your DB tables.

Here is a method that could be added to a class **Product** allowing a custom search by 'id' and text within 'description':

``` php

    <?php
    namespace Whatever; // same as for Entity calss
    
    use Mattsmithdev\PdoCrudRepo\DatabaseManager;
    use Mattsmithdev\PdoCrudRepo\DatabaseTableRepository;
    
    class ProductRepository extends DatabaseTableRepository
    {
    
        /**
         * illustrate custom PDO DB method
         * in this case we search for products with an id >= $minId, and whose descrption contains $searchText
         *
         * @param $minId
         * @param $searchText
         *
         * @return array
         */
        public function getAllAboveMinIdContainsString($minId, $searchText)
        {
            $db = new DatabaseManager();
            $connection = $db->getDbh();
    
            // wrap wildcard '%' around the search text for the SQL query
            $searchText = '%' . $searchText . '%';
    
            $sql = 'SELECT * FROM product WHERE (description LIKE :searchText) AND (id > :minId)';
    
            $statement = $connection->prepare($sql);
            $statement->bindParam(':minId', $minId, \PDO::PARAM_INT);
            $statement->bindParam(':searchText', $searchText, \PDO::PARAM_STR);
            $statement->setFetchMode(\PDO::FETCH_CLASS, $this->getClassNameForDbRecords());
            $statement->execute();
    
            $products = $statement->fetchAll();
    
            return $products;
        }
```
    
and here is an example of its usage, in a controller function:

``` php

    // get products from DB as array of Product objects - id > minId, description containing $searchText
    $minId = 2;
    $searchText = 'er';
    $productRepository = new ProductRepository();
    $products = $productRepository->getAllAboveMinIdContainsString($minId, $searchText);

    // outputs something like:
    //  [5] pliers
    //  [7] hammer
    foreach ($products as $product){
        print '<p>';
        print 'id [' . $product->getId() . '] ';
        print $product->getDescription();
    }

    //  [1] nut -- not listed due to search criteria
```

## Migrations and fixtures

Here are examples of a simple scripts to update a table schema and insert some initial data.

If we have added a `createAndInsert(...)` method to our Repository class then it can be as simple as this:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tudublin\MovieRepository;

$movieRepository = new MovieRepository();

// (1) drop then re-create table
$movieRepository->resetTable();

// (2) create objects
$movieRepository->createAndInsert('Jaws', 9.99, 'horror');
$movieRepository->createAndInsert('Jumanji', 7, 'entertainment');

// (3) test objects are there
$movies = $movieRespository->findAll();
print '<pre>';
var_dump($movies);
```

If we don't have  `createAndInsert(...)` method then we have to create each object and then insert it into the DB table:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tudublin\Movie;
use Tudublin\MovieRepository;

$movieRespository = new MovieRepository();

// (1) drop then create table
$movieRespository->resetTable();

// (3) create objects
$m1 = new Movie();
$m1->setTitle('Jaws');
$m1->setPrice(9.99);
$m1->setCategory('horror');

$m2 = new Movie();
$m2->setTitle('Jumanji');
$m2->setPrice(9.99);
$m2->setCategory('entertainment');

// (3) insert objects into DB
$movieRespository->insert($m1);
$movieRespository->insert($m2);

// (4) test objects are there
$movies = $movieRespository->findAll();
print '<pre>';
var_dump($movies);
```

OUTPUT:

```bash
--------------- DatabaseTableRepository->createTable() ----------------
NOTE:: Looking for a constant CREATE_TABLE_SQL defined in the entity class associated with this repository
-----------------------------------------------------------------------
<pre>/Users/matt/Documents/github/pdo-crud-for-free-repositories/db/movieMigrationAndFixtures.php:35:
array(2) {
  [0] =>
  class Tudublin\Movie#8 (4) {
    private $id =>
    string(1) "1"
    private $title =>
    string(4) "Jaws"
    private $price =>
    string(4) "9.99"
    private $category =>
    string(6) "horror"
  }
  [1] =>
  class Tudublin\Movie#9 (4) {
    private $id =>
    string(1) "2"
    private $title =>
    string(7) "Jumanji"
    private $price =>
    string(4) "9.99"
    private $category =>
    string(13) "entertainment"
  }
}
```
## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email dr_matt_smith@me.com instead of using the issue tracker.

## Credits

- [Matt Smith](https://github.com/dr-matt-smith)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mattsmithdev/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/mattsmithdev/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/mattsmithdev/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/mattsmithdev/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mattsmithdev/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mattsmithdev/:package_name
[link-travis]: https://travis-ci.org/mattsmithdev/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/mattsmithdev/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/mattsmithdev/:package_name
[link-downloads]: https://packagist.org/packages/mattsmithdev/:package_name
[link-author]: https://github.com/mattsmithdev
[link-contributors]: ../../contributors
