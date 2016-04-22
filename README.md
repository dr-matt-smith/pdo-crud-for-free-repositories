# pdo-crud-for-free+(repositories)

Note - this is essentially an alternative approach to the [pdo-crud-for-free](link-packagist) package


This package provides a few classes to try to give programmers using PDO (with MySQL) in a simple way some instance CRUD (create-read-update-delete) method, 'for free', simply by subclassing **\Mattsmithdev\PdoCrud\DatabaseTable**.

All code is (intended :-) to follow PSR-1, PSR-2 coding standards. Classes are following the PSR-4 autoloading standard.

## Install

Via Composer

``` bash
$ composer require mattsmithdev/pdo-crud-for-free-repositories
```


## Usage

This example assumes you have a MySQL DB table named 'products', with columns 'id' and 'description'. You need to write a corresponding class 'Product' (note capital first letter ...).

``` php

// file: /src/Product.php
namespace <MyNameSpace>;

class Product extends \Mattsmithdev\PdoCrud\DatabaseTable 
{
    // private properties with EXACTLY same names as DB table columns
    private $id;
    private $description;
    
    public function getDescription()
    {
        return $this->description;
    }
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

// get all products from DB as array of Product objects
$products = \<MyNameSpace>\Product::getAll();

// outputs something like:
//  hammer, nail, nuts, bolts
foreach ($products as $product){
    print $product->getDescription() . ', ';
}
```

For more details see below. Also there is a full sample web application project on GitGub at:
 (pdo-crud-for-free-example-project)[https://github.com/dr-matt-smith/pdo-crud-for-free-example-project]

# More detailed usage instructions (and important assumptions)


## ASSUMPTION 1: lowerCamelCase - DB table column names matching PHP Class properties
This tool assumes your database table column names, and their corresponding PHP private class properties are named consistently in 'lowerCamelCase'
e.g.

    id
    title
    category
    price

## ASSUMPTION 2: lower case plural DB table name mapping to upper case singular PHP class name
If you have a DB table '**products**' this will correspond to a PHP class '**Product**'

table names are named lower case, and are plural nouns, e.g '**users**'
PHP class names are named with a capital first letter, and are singular nouns, e.g. '**User**'

## ASSUMPTION 3: no constructor for your PHP classes
due to the nature of PDO populating properties of objects when DB rows are converted into object instances
do not have a constructor for the PHP classes that correspond to your DB tables

so you'd create a new object, and use the objects public 'setter' methods
e.g.
$p = new Product();
$p->setDescription('hammer');
$p->setPrice(9.99);
etc.


## step 1: create your DB tables
e.g. create your tables (with integer 'id' field, primary key, auto-increment)

e.g. SQL table to store DVD data

    id:int (primary key, autoincrement)
    title:text
    category:text
    price:float

## step 2: create a corresponding PHP class, and subclass from Mattsmithdev\PdoCrud\DatabaseTable
e.g.

    <?php
    namespace Whatever;
    
    use Mattsmithdev\PdoCrud\DatabaseTable;
    
        class Dvd extends DatabaseTable
        {
            private $id;
            private $title;
            private $category;
            private $price;
            
            // and public getters and setters ...
            
## step 3: now use the 'magically appearing' static DB CRUD methods

e.g. to get an array of all dvd records from table 'dvds' just write:

    $dvds = Dvd::getAll();
    

## ::getAll()
this method returns an array of objects for each row of the corresponding DB table
e.g.

    // array of Dvd objects, populated from database table 'dvds'
    $dvds = Dvd::getAll();

## ::getOneById($id)
this method returns one object of class for the corresponding DB table record with the given 'id'
(returns 'null' if no such record exists with that primary key id)
e.g.

    // one Dvd object (or 'null'), populated by row in database table 'dvds' with id=27
    $dvds = Dvd::getOneById(27);

## ::delete($id)
this method deletes the record corresponding to the given 'id'
returns true/false depending on success of the deletion
e.g.

    // delete row in database table 'dvds' with id=12
    $deleteSuccess = Dvd::delete(12);
    
## ::insert($dvd)
this method adds a new row to the database, based on the contents of the provided object
(any 'id' in this object is ignored, since the table is auto-increment, so it's left to the DB to assign a new, unique 'id' for new records)
returns the 'id' of the new record (or -1 if error when inserting)
e.g.

    // delete row in database table 'dvds' with id=12
    $dvd = new Dvd();
    $dvd->setTitle('Jaws II');
    $dvd->setCategory('thriller');
    $dvd->setPrice(9.99);
    
    // create the new Dvd row
    $id = Dvd::insert($dvd);
    
    // decision based on success/failure of insert
    if ($id < 0){
        // error action
    } else {
        // success action
    }
    
    
## ::update($dvd)
this method adds a UPDATES an existing row in the database, based on the contents of the provided object
returns true/false depending on success of the deletion

e.g.

    // update DB record for object 'dvd'
    $updateSuccess = Dvd:update($dvd);
    
            
## ::searchByColumn($columnName, $searchText))
perform an SQL '%' wildcard search on the given column with the given search text
returns an array of objects that match an SQL 'LIKE' query 

e.g.

    // get all Dvds with 'jaws' in the title
    $jawsDvds = Dvd::searchByColumn('title', 'jaws');

## custom PDO methods
If the 'free' DB methods are insufficient, it's easy to add your own methods to your PHP classes that correspond to your DB tables.

Here is a method that could be added to a class **Product** allowing a custom search by 'id' and text within 'descrition':

    /**
     * illustrate custom PDO DB method
     * in this case we search for products with an id >= $minId, and whose descrption contains $searchText
     *
     * @param $minId
     * @param $searchText
     *
     * @return array
     */
    public static function customSearch($minId, $searchText)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        // wrap wildcard '%' around the serach text for the SQL query
        $searchText = '%' . $searchText . '%';

        $sql = 'SELECT * FROM products WHERE (description LIKE :searchText) AND (id > :minId)';

        $statement = $connection->prepare($sql);
        $statement->bindParam(':minId', $minId, \PDO::PARAM_INT);
        $statement->bindParam(':searchText', $searchText, \PDO::PARAM_STR);
        $statement->setFetchMode(\PDO::FETCH_CLASS, '\\' . __CLASS__);
        $statement->execute();

        $products = $statement->fetchAll();

        return $products;
    }
    
and here is an example of its usage, in a controller function:

    // get products from DB as array of Product objects - id > minId, description containing $searchText
    $minId = 2;
    $searchText = 'er';
    $products = Product::customSearch($minId, $searchText);

    // outputs something like:
    //  [5] pliers
    //  [7] hammer
    foreach ($products as $product){
        print '<p>';
        print 'id [' . $product->getId() . '] ';
        print $product->getDescription();
    }

    //  [1] nut -- not listed due to search criteria

## Change log

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

- [Matt Smith][https://github.com/dr-matt-smith]

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
