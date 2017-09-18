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
    const TYPE_SQLITE = 0;
    const TYPE_MYSQL = 1;

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
        try {
            // DSN - the Data Source Name - requred by the PDO to connect
            if(defined('DB_TYPE') && DB_TYPE == self::TYPE_SQLITE) {
                $dsn = 'sqlite:' . DB_PATH;
            } else {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
            }

            // Set options
            $options = array(
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            );

            if(defined('DB_TYPE') && DB_TYPE == self::TYPE_SQLITE) {
                $this->dbh = new \PDO($dsn);
            } else {
                $this->dbh = new \PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            print 'sorry - a database error occurred - please contact the site administrator ...';
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
