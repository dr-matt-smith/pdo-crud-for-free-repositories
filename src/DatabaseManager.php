<?php


namespace Mattsmithdev\PdoCrudRepo;

use Symfony\Component\Dotenv\Dotenv;


/**
 * Class DatabaseManager - make things easy to work with MySQL DBs and PDO
 * @package Mattsmithdev
 */
class DatabaseManager
{
    public const DATBASE_EXISTS = 1007;
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

        // -- first -- create DB connection with no DB selected, and try to create it --
        try {
            // DSN - the Data Source Name - requred by the PDO to connect
            $dsn = 'mysql:host=' . $this->host . ':' . $this->port;

            // Set options
            $options = array(
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            );
            $this->dbh = new \PDO($dsn, $this->user, $this->pass, $options);

            $sql = 'CREATE DATABASE ' . $this->dbname;
            $statement = $this->dbh->prepare($sql);
            $success = $statement->execute();

        } catch (\PDOException $e) {
            if($this->databaseExists($e)){
                // database already exists - all good
//                print "using database '{$this->dbname}' \n";

            } else {
                // some other error
                $this->error = $e->getMessage();
                print 'sorry - a database error occured - please contact the site administrator ...';
                print '<br>';
                print  $e->getMessage();

            }
        }

        if(!empty($success)){
            print "database '{$this->dbname}'' did not exist, so new schema created \n";
        }

        // -- second -- now DB exists should be able to connect to it
        try {
            $dsn = 'mysql:host=' . $this->host . ':' . $this->port . ';dbname=' . $this->dbname;
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
            return;
        }
    }

    private function databaseExists(\PDOException $e)
    {
        $errorCode = $e->errorInfo[1];
        return self::DATBASE_EXISTS == $errorCode;
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
            throw new \Exception("\n\n ********** missing MYSQL_HOST environment value - or perhaps missing .env file altogether ...\n\n");
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
