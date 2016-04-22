<?php
/**
 * Created by PhpStorm.
 * User: mattsmithdev
 * Date: 18/02/2016
 * Time: 11:58
 *
 * based on PDO tutorial at URL:
 * http://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
 */

namespace Mattsmithdev\PdoCrudRepo;

/**
 * Class DatabaseManager - make things easy to work with MySQL DBs and PDO
 * @package Mattsmithdev
 */
class DatabaseManager
{
    /**
     * get host name from config constant
     * @var string
     */
    private $host = DB_HOST;

    /**
     * get DB username from config constant
     * @var string
     */
    private $user = DB_USER;

    /**
     * get DB password from config constant
     * @var string
     */
    private $pass = DB_PASS;

    /**
     * get DB name from config constant
     * @var string
     */
    private $dbname = DB_NAME;

    /**
     * the DataBase Handler is our db connection object
     * @var database handler
     */
    private $dbh;

    /**
     * any error generated
     * @var string
     */
    private $error;

    public function __construct()
    {
        // DSN - the Data Source Name - requred by the PDO to connect
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        try {
            // Set options
            $options = array(
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            );
            $this->dbh = new \PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            print 'sorry - a database error occured - please contact the site administrator ...';
            print '<br>';
            print  $e->getMessage();
        }
    }

    public function getDbh()
    {
        return $this->dbh;
    }

    public function getError()
    {
        return $this->error;
    }
}
