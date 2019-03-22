# pdo-crud-for-free-repositories+(repositories)

[![Build Status](https://travis-ci.org/dr-matt-smith/pdo-crud-for-free-repositories.svg?branch=master)](https://travis-ci.org/dr-matt-smith/pdo-crud-for-free-repositories) [![Latest Stable Version](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/v/stable)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories) [![Total Downloads](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/downloads)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories) [![Latest Unstable Version](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/v/unstable)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories) [![License](https://poser.pugx.org/mattsmithdev/pdo-crud-for-free-repositories/license)](https://packagist.org/packages/mattsmithdev/pdo-crud-for-free-repositories)


Note - this is essentially an alternative approach to the [pdo-crud-for-free](link-packagist) package


This package provides a few classes to try to give programmers using PDO (with MySQL) in a simple way some instance CRUD (create-read-update-delete) method, 'for free', simply by creating an entity repository sub-class of Mattsmithdev\PdoCrudRepo\DatabaseTableRepository.

All code is (intended :-) to follow PSR-1, PSR-2 coding standards. Classes are following the PSR-4 autoloading standard.

## Install

Via Composer

``` bash
$ composer require mattsmithdev/pdo-crud-for-free-repositories
```


## Usage

This example assumes you have a MySQL DB table named 'dvds', with columns 'id' and 'description'. You need to write a corresponding class 'Dvd' (note capitalization on the first letter). Also you need to write a repository class to work between your PHP class and is correspnding table, in this example the repository class is named 'DvDRepository':

``` php
    // file: /src/Dvd.php
    namespace <MyNameSpace>;
    
    class Dvd
    {
        // private properties with EXACTLY same names as DB table columns
        private $id;
        private $title;
        
        public function getTitle()
        {
            return $this->title;
        }
    }
```


``` php
    // file: /src/DvdRepository.php
    namespace Evote;
    
    use Mattsmithdev\PdoCrudRepo\DatabaseManager;
    use Mattsmithdev\PdoCrudRepo\DatabaseTableRepository;
    
    class DvdRepository extends DatabaseTableRepository
    {
        public function __construct()
        {
            parent::__construct('Evote', 'Dvd', 'dvds');
        }
```


``` php
    // file: /public-web/index.php or /src/SomeController->method()
    
    require_once __DIR__ . '/<PATH_TO_AUTLOAD>';
    
    // the DatabaseManager class needs the following 4 constants to be defined in order to create the DB connection
    define('DB_HOST', '<host>');
    define('DB_USER', '<db_username>');
    define('DB_PASS', '<db_userpassword>');
    define('DB_NAME', '<db_name>');
    
    // create a repository object
    $dvdRepository = new DvdRepository();
    
    // get all records from DB as an array of Dvd objects
    $dvds = $dvdRepository->getAll();
    
    // output each Dvd object as HTML lines in the form 'title = Jaws II'
    foreach($dvds as $dvd){
        /**
         * @var $dvd Dvd
         */
        print 'id = ' . $dvd->getId();
        print '<br>';
        print 'title = ' . $dvd->getTitle();
        print '<br>';
        print 'category' . $dvd->getCategory();
        print '<p>';
    
    }
```

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

## ASSUMPTION 2: No constructor for your PHP classes.
due to the nature of PDO populating properties of objects when DB rows are converted into object instances
do not have a constructor for the PHP classes that correspond to your DB tables

so you'd create a new object, and use the objects public 'setter' methods
e.g.

``` php

    $p = new Product();
    $p->setDescription('hammer');
    $p->setPrice(9.99);
    etc.
```


## ASSUMPTION 3: Each class has an integer, `id` property
Each Entity class should have an integer `id` property.
This property should be an `AUTO_INCREMENT` primary key in the database table schema, e.g.

```sql
    -- SQL statement to create the table --
    create table if not exists Product (
        id integer primary key AUTO_INCREMENT,
        descrition text,
        price float
    );
```


## Step 1: Create your DB tables.
e.g. create your tables (with integer 'id' field, primary key, auto-increment)

e.g. SQL table to store DVD data

    id:int (primary key, autoincrement)
    title:text
    category:text
    price:float

## Step 2: Create a corresponding PHP class, and subclass from Mattsmithdev\PdoCrud\DatabaseTable
e.g.

``` php
    <?php
    namespace Whatever;
    

    class Dvd
    {
        private $id;
        private $title;
        private $category;
        private $price;
        
        // and public getters and setters ...
```
            
## Step 3: Create a repository class mapping your DB table to your PHP entity class.

e.g. create repository class DvdRepository mapping from table `dvds` to PHP class `Evote\Dvd`:

``` php

    <?php
    namespace Evote;
    
    use Mattsmithdev\PdoCrudRepo\DatabaseManager;
    use Mattsmithdev\PdoCrudRepo\DatabaseTableRepository;
    
    class DvdRepository extends DatabaseTableRepository
    {
        public function __construct()
        {
            parent::__construct('Evote', 'Dvd', 'dvds');
        }
    }
    
```    

## Step 4: Now use the 'magically appearing' static DB CRUD methods.

e.g. to get an array of all dvd records from table 'dvds' just write:

``` php

    $dvdRepository = new DvdRepository();
    $dvds = $dvdRepository->getAll();
    
```    

## ::getAll()
this method returns an array of objects for each row of the corresponding DB table
e.g.

``` php
    // array of Dvd objects, populated from database table 'dvds'
    $dvdRepository = new DvdRepository();
    $dvds = $dvdRepository->getAll();
```

## ::getOneById($id)
this method returns one object of class for the corresponding DB table record with the given 'id'
(returns 'null' if no such record exists with that primary key id)
e.g.

``` php
    // one Dvd object (or 'null'), populated by row in database table 'dvds' with id=27
    $dvdRepository = new DvdRepository();
    $dvd = $dvdRepository->getOneById(27);
```

## ::delete($id)
this method deletes the record corresponding to the given 'id'
returns true/false depending on success of the deletion
e.g.

``` php
    // delete row in database table 'dvds' with id=12
    $dvdRepository = new DvdRepository();
    $deleteSuccess = $dvdRepository->delete(12);
```
    
## ::create($dvd)
this method adds a new row to the database, based on the contents of the provided object
(any 'id' in this object is ignored, since the table is auto-increment, so it's left to the DB to assign a new, unique 'id' for new records)
returns the 'id' of the new record (or -1 if error when inserting)
e.g.

``` php

    // delete row in database table 'dvds' with id=12
    $dvd = new Dvd();
    $dvd->setTitle('Jaws II');
    $dvd->setCategory('thriller');
    $dvd->setPrice(9.99);
    
    // create the new Dvd row
    $dvdRepository = new DvdRepository();
    $id = $dvdRepository->create($dvd);
    
    // decision based on success/failure of insert
    if ($id < 0){
        // error action
    } else {
        // success action
    }
```    
    
## ::update($dvd)
This method adds a UPDATES an existing row in the database, based on the contents of the provided object
returns true/false depending on success of the deletion

e.g.

``` php
    // update DB record for object 'dvd'
    $dvdRepository = new DvdRepository();
    $updateSuccess = $dvdRepository->update($dvd);
```    
            
## ::searchByColumn($columnName, $searchText))
Perform an SQL '%' wildcard search on the given column with the given search text
returns an array of objects that match an SQL 'LIKE' query 

e.g.

``` php
    // get all Dvds with 'jaws' in the title
    $dvdRepository = new DvdRepository();
    $jawsDvds = $dvdRepository->searchByColumn('title', 'jaws');
```

## custom PDO methods
If the 'free' DB methods are insufficient, it's easy to add your own methods to your PHP classes that correspond to your DB tables.

Here is a method that could be added to a class **Product** allowing a custom search by 'id' and text within 'descrition':

``` php

    /**
     * illustrate custom PDO DB method
     * in this case we search for products with an id >= $minId, and whose descrption contains $searchText
     *
     * @param $minId
     * @param $searchText
     *
     * @return array
     */
    public function customSearch($minId, $searchText)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        // wrap wildcard '%' around the search text for the SQL query
        $searchText = '%' . $searchText . '%';

        $sql = 'SELECT * FROM products WHERE (description LIKE :searchText) AND (id > :minId)';

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
    $dvdRepository = new DvdRepository();
    $products = $dvdRepository->customSearch($minId, $searchText);

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
