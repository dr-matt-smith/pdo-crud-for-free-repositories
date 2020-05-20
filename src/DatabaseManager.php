<?php


namespace Mattsmithdev\PdoCrudRepo;

use Symfony\Component\Dotenv\Dotenv;


/**
 * Class DatabaseManager - make things easy to work with MySQL DBs and PDO
 * @package Mattsmithdev
 */
class DatabaseManager
{
    /**
     * host name
     * @var string
     */
    private $host;

    /**
     * host port number
     * @var string
     */
    private $port;

    /**
     * DB username
     * @var string
     */
    private $user;

    /**
     * DB password
     * @var string
     */
    private $pass;

    /**
     * DB name
     * @var string
     */
    private $dbname;

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
        $this->loadCredentialsFromDotEnv();

        // DSN - the Data Source Name - requred by the PDO to connect
        $dsn = 'mysql:host=' . $this->host . ':' . $this->port . ';dbname=' . $this->dbname;
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

    private function loadCredentialsFromDotEnv()
    {
        // load dotenv file
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../../../.env');

        // extract values
        $this->host = $_ENV['MYSQL_HOST'];
        $this->port = $_ENV['MYSQL_PORT'];
        $this->user = $_ENV['MYSQL_USER'];
        $this->pass = $_ENV['MYSQL_PASSWORD'];
        $this->dbname = $_ENV['MYSQL_DATABASE'];

        if(!$this->host){
            throw new \Exception("\n\n ********** missing MYSQL_HOST environment value - or perhaps mnissing .env file altogether ...\n\n");
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
