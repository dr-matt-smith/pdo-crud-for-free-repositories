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


    public function getAll()
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $sql = 'SELECT * from ' . $this->tableName;

        $statement = $connection->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->classNameForDbRecords);
        $statement->execute();

        $objects = $statement->fetchAll();
        return $objects;
    }

    public function getOneById($id)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $statement = $connection->prepare('SELECT * from ' . $this->tableName . ' WHERE id=:id');
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

        $statement = $connection->prepare('DELETE from ' . $this->tableName . ' WHERE id=:id');
        $statement->bindParam(':id', $id, \PDO::PARAM_INT);
        $queryWasSuccessful = $statement->execute();
        return $queryWasSuccessful;
    }


    public function searchByColumn($columnName, $searchText)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        // wrap wildcard '%' around the serach text for the SQL query
        $searchText = '%' . $searchText . '%';

        $statement = $connection->prepare('SELECT * from ' . $this->tableName . ' WHERE ' . $columnName . ' LIKE :searchText');
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
    public function create($object)
    {
        $db = new DatabaseManager();
        $connection = $db->getDbh();

        $objectAsArrayForSqlInsert = DatatbaseUtility::objectToArrayLessId($object);
        $fields = array_keys($objectAsArrayForSqlInsert);
        $insertFieldList = DatatbaseUtility::fieldListToInsertString($fields);
        $valuesFieldList = DatatbaseUtility::fieldListToValuesString($fields);

        $statement = $connection->prepare('INSERT into '. $this->tableName . ' ' . $insertFieldList . $valuesFieldList);
        $statement->execute($objectAsArrayForSqlInsert);

        $queryWasSuccessful = ($statement->rowCount() > 0);
        if($queryWasSuccessful) {
            return $connection->lastInsertId();
        } else {
            return -1;
        }
    }


    /**
     * insert new record into the DB table
     * returns new record ID if insertation was successful, otherwise -1
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

        $sql = 'UPDATE '. $this->tableName . ' SET ' . $updateFieldList  . ' WHERE id=:id';
        $statement = $connection->prepare($sql);

        // add 'id' to parameters array
        $objectAsArrayForSqlInsert['id'] = $id;

        $queryWasSuccessful = $statement->execute($objectAsArrayForSqlInsert);

        return $queryWasSuccessful;
    }

}
