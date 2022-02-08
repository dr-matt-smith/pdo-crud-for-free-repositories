<?php
namespace Mattsmithdev\PdoCrudRepo;

class DatabaseTableRepository
{
    /**
     * the (fully namespaced) name of the class corresponding to the database table to be worked with
     * e.g. \MyCompany\Product
     *
     * @var string
     */
    private $classNameForDbRecords;

    /**
     * the name of the database table to be worked with
     * @var string
     */
    private $tableName;

    /**
     * DatabaseTableRepository constructor.
     * @param array $params
     *
     * possible params:
     *      'namespace' e.g. 'MyNameSpace'
     *      'dbClass' e.g 'Movie'
     *      'tableName' e.g. 'movie'
     *
     * assumption:
     *      namespace of dbTable is same as namespace of repository class
     *      repository class is dbTable name with suffix 'Repository', e.g. Movie, MovieRepository
     *      table name is lower case version of class name, e.g. table 'movie' for class 'Movie'
     */
    public function __construct(Array $params = [])
    {
        // e.g.
        // My\NameSpace\EntityRepository
        //
        // defaults are as follows:
        // $namespace = My\NameSpace - entity class in same namespace as repository class
        // $className = Entity - entity name is repository class less, less the word 'Repository'
        // $tableName = entity - table name is same as entity class name, but all in loser case
        //
        // IF the above 3 defaults are true,
        // THEN the repository class does not need a constructor at all :-)

        // (1) create default values
        // namespace
        try {
            $reflector = new \ReflectionClass(get_class($this));
            $namespace  = $reflector->getNamespaceName();
            $shortName = $reflector->getShortName();
            $className = str_replace('Repository', '', $shortName);
            $tableName = strtolower($className);
        } catch (\Exception $e) {
            $namespace = 'error-trying-to-infer-namespace';
            $className = 'error-trying-to-infer-classname';
            $tableName = 'error-trying-to-infer-tablename';

        }

        // (2) use provided params, if found
        if(isset($params['namespace'])){
            $namespace = $params['namespace'];
        }
        if(isset($params['className'])){
            $className = $params['className'];
        }
        if(isset($params['tableName'])){
            $tableName = $params['tableName'];
        }

        // store namespace class and db table name into properties
        $this->classNameForDbRecords = $namespace . '\\' . $className;
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getClassNameForDbRecords()
    {
        return $this->classNameForDbRecords;
    }

    /**
     * @param string $classNameForDbRecords
     */
    public function setClassNameForDbRecords($classNameForDbRecords)
    {
        $this->classNameForDbRecords = $classNameForDbRecords;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }


    public function findAll()
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $sql = 'SELECT * from :table';
        $sql = str_replace(':table', $this->tableName, $sql);

        $statement = $connection->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->classNameForDbRecords);
        $statement->execute();

        $objects = $statement->fetchAll();
        return $objects;
    }

    public function find($id)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $sql = 'SELECT * from :table WHERE id=:id';
        $sql = str_replace(':table', $this->tableName, $sql);

        $statement = $connection->prepare($sql);
        $statement->bindParam(':id', $id, \PDO::PARAM_INT);
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->classNameForDbRecords);
        $statement->execute();

        if ($object = $statement->fetch()) {
            return $object;
        } else {
            return null;
        }
    }


    /**
     * delete record for given ID - return true/false depending on delete success
     * @param $id
     *
     * @return bool
     */

    public function delete($id)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $sql = 'DELETE from :table WHERE id=:id';
        $sql = str_replace(':table', $this->tableName, $sql);

        $statement = $connection->prepare($sql);
//        $statement->bindParam(':table',  $this->tableName);
        $statement->bindParam(':id', $id, \PDO::PARAM_INT);

        $queryWasSuccessful = $statement->execute();
        return $queryWasSuccessful;
    }



    /**
     * delete all records- return true/false depending on delete success
     *
     * @return bool
     */

    public function deleteAll()
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $sql = 'TRUNCATE TABLE :table';
        $sql = str_replace(':table', $this->tableName, $sql);

        $statement = $connection->prepare($sql);
//        $statement->bindParam(':table',  $this->tableName);

        $queryWasSuccessful = $statement->execute();
        return $queryWasSuccessful;
    }


    public function searchByColumn($columnName, $searchText)
    {
        $columnName = filter_var($columnName, FILTER_SANITIZE_STRING);

        $db = new DatabaseManager();
        $connection = $db->getDbh();

        // wrap wildcard '%' around the serach text for the SQL query
        $searchText = '%' . $searchText . '%';

        $sql = 'SELECT * from :table WHERE :column LIKE :searchText';
        $sql = str_replace(':table', $this->tableName, $sql);
        $sql = str_replace(':column', $columnName, $sql);

        $statement = $connection->prepare($sql);
//        $statement->bindParam(':column', $columnName, \PDO::PARAM_STR);
        $statement->bindParam(':searchText', $searchText, \PDO::PARAM_STR);
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->classNameForDbRecords);
        $statement->execute();

        $objects = $statement->fetchAll();

        return $objects;
    }


    /**
     * insert new record into the DB table
     * returns new record ID if insertion was successful, otherwise -1
     * @param Object $object
     * @return integer
     */
    public function insert($object)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $objectAsArrayForSqlInsert = DatatbaseUtility::objectToArrayLessId($object);
        $fields = array_keys($objectAsArrayForSqlInsert);
        $insertFieldList = DatatbaseUtility::fieldListToInsertString($fields);
        $valuesFieldList = DatatbaseUtility::fieldListToValuesString($fields);

        $sql = 'INSERT into :table :insertFieldList :valuesFieldList';
        $sql = str_replace(':table', $this->tableName, $sql);
        $sql = str_replace(':insertFieldList', $insertFieldList, $sql);
        $sql = str_replace(':valuesFieldList', $valuesFieldList, $sql);

        $statement = $connection->prepare($sql);
        $statement->execute($objectAsArrayForSqlInsert);
        $queryWasSuccessful = ($statement->rowCount() > 0);
        if($queryWasSuccessful) {
            return $connection->lastInsertId();
        } else {
            return -1;
        }
    }

    /**
     * given an array of object, loop through them and insert them each into the DB table
     *
     * @param array $objects]
     */
    public function insertMany(array $objects)
    {
        foreach($objects as $object){
            $this->insert($object);
        }
    }


    /**
     * insert new record into the DB table
     * returns new record ID if insertion was successful, otherwise -1
     *
     * @param $object
     *
     * @return bool
     */
    public function update($object)
    {
        $id = $object->getId();

        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $objectAsArrayForSqlInsert = DatatbaseUtility::objectToArrayLessId($object);
        $fields = array_keys($objectAsArrayForSqlInsert);
        $updateFieldList = DatatbaseUtility::fieldListToUpdateString($fields);

        $sql = 'UPDATE :table SET :updateFieldList WHERE id=:id';
        $sql = str_replace(':table', $this->tableName, $sql);
        $sql = str_replace(':updateFieldList', $updateFieldList, $sql);

        $statement = $connection->prepare($sql);

        // add 'id' to parameters array
        $objectAsArrayForSqlInsert['id'] = $id;

        $queryWasSuccessful = $statement->execute($objectAsArrayForSqlInsert);

        return $queryWasSuccessful;
    }


    /**
     * drop the table associated with this repository
     *
     * @return bool
     */
    public function dropTable()
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $sql = 'DROP TABLE IF EXISTS :table';
        $sql = str_replace(':table', $this->tableName, $sql);

        $statement = $connection->prepare($sql);
//        $statement->execute();

        $queryWasSuccessful = $statement->execute();

        return $queryWasSuccessful;
    }

    /**
     * create the table associated with this repository
     *
     * @param $sql - optional SQL CREATE statement
     * DEFAULT: Look for a constant CREATE_TABLE_SQL defined in the entity class associated with this repository
     *
     * @return bool
     *
EXAMPLE OF SQL needed in Entity class:
 const CREATE_TABLE_SQL =
    <<<HERE
    CREATE TABLE IF NOT EXISTS movie (
    id integer PRIMARY KEY AUTO_INCREMENT,
    title text,
    price float,
    category text
    )
    HERE;

     */

    public function createTable($sql = null)
    {
        if(null == $sql){
            $sql = $this->getSqlToCreateTable();
        }
        try{
            $db = new DatabaseManager();
            $connection = $db->getDbh();

            $statement = $connection->prepare($sql);
            $statement->execute();
        } catch (\PDOException $e){
            $this->error = $e->getMessage();
            print "*** \n";
            print "*** sorry - a database error occured - please contact the site administrator ***\n";
            print "trying to execute this SQL: \n";
            print "$sql \n";
            print  $e->getMessage();
            print "*** \n";
            print "*** \n";
            return;
        }
    }

    public function getSqlToCreateTable(): string
    {
        // try to get from constant CREATE_TABLE_SQL in entity class
        if($this->hasConstant()) {
            return $this->classNameForDbRecords::CREATE_TABLE_SQL;
        }

        // try to infer from types of entity class properties
        $sql = $this->inferSqlFromPropertyTypes();

        if(!empty($sql)){
            return $sql;
        }

        throw new \Exception('cannot find or infer SQL to create table for class ' . $this->classNameForDbRecords);
    }

    public function hasConstant(string $identifier = 'CREATE_TABLE_SQL')
    {
        $reflectionClass = new \ReflectionClass($this->classNameForDbRecords);
        $constants = $reflectionClass->getConstants();
        return array_key_exists($identifier, $constants);
    }

    public function resetTable($sql = null)
    {
        $this->dropTable();
        $this->createTable($sql);
        $this->deleteAll();
    }

    /**
     * @return string
     * @throws \ReflectionException
     *
     * return SQL table creation string such as:
     *  CREATE TABLE IF NOT EXISTS movie (
     *      id integer PRIMARY KEY AUTO_INCREMENT,
     *      title text,
     *      price float,
     *      category text
     *  )
     */
    public function inferSqlFromPropertyTypes(): string
    {
        $dbUtility = new DatatbaseUtility();
        $sql = '';

        $reflectionClass = new \ReflectionClass($this->classNameForDbRecords);
        $refletionProperties = $reflectionClass->getProperties();

        $sqlTypes = [];
        foreach ($refletionProperties as $refletionProperty) {
            $propertyName = $refletionProperty->name;
            $type = $refletionProperty->getType()->getName();


            $type = $dbUtility->dbDataType($type);

            // if not 'id' add to array
            if('id' != $propertyName){
                $sqlTypes[$propertyName] = $type;
            }
        }


        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->tableName . ' ('
            . 'id integer PRIMARY KEY AUTO_INCREMENT, '
            . $dbUtility->dbPropertyTypeList($sqlTypes)
            . ')';

        return $sql;
    }

}
