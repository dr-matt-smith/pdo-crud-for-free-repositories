<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 24/02/2016
 * Time: 22:31
 */
namespace Mattsmithdev\PdoCrudRepo;

class DatatbaseUtility
{

    /**
     * convert an Object into an associate array, and remove the first element (the 'id')
     * e.g. convert from this:
     *  Itb\Message Object
     *  (
     *      [id:Itb\Message:public] => (null or whatever)
     *      [text:Itb\Message:public] => hello there
     *      [user:Itb\Message:public] => matt
     *      [timestamp:Itb\Message:public] => 1456340266
     *  )
     *
     * to this:
     * Array
     * (
     *      [text] => hello there
     *      [user] => matt
     *      [timestamp] => 1456341484
     * )
     *
     * this is a convenient way to INSERT objects into autoincrement tables (so we don't want to pass the ID value - we want the DB to choose a new ID for us...)
     *
     * @param $object
     *
     * @return array
     */
    public static function objectToArrayLessId($object)
    {
        // convert object into associative array
        $objectAsArray = (array)$object;

        $objectsAsArrayLessNamespaces = self::removeNamespacesFromKeys($objectAsArray, $object);

        // remove the 'id' element
        unset($objectsAsArrayLessNamespaces['id']);

        // return this array
        return $objectsAsArrayLessNamespaces;
    }


    /**
     *
     * Given an associative array of key=>value pairs, where each key is in the form '\0Namespace\Class\0property'
     * (and an object from which the array was generated)
     * output the array, but with only the property names as the array keys
     * i.e remove the 'Namespace\Class' prefix from each element's array key
     *
     * note we use FILTER_SANITISE_STRING to remove the \0 characters
     *
     * e.g. convert from this:
     * Array
     * (
     *      [\0Itb\Message\0text] => hello there
     *      [\0Itb\Message\0user] => matt
     *      [\0Itb\Message\0timestamp] => 1456340361
     * )
     *
     * to this:
     * Array
     * (
     *      [text] => hello there
     *      [user] => matt
     *      [timestamp] => 1456341484
     * )
     *
     * @param array $properties array of properties (where keys are prefixed with namespace and class)
     * @param Object $object reference to object from which property array originated
     *
     * @return array
     */
    public static function removeNamespacesFromKeys($properties, $object)
    {
        // get the class name, e.g. 'Itb\Message'
        $className = get_class($object);

        // empty array for new values
        $propertiesWithSimpleKeys = [];
        foreach ($properties as $key=>$value) {
            $simpleKey = str_replace($className, '', $key);
            $simpleKey = filter_var($simpleKey, FILTER_SANITIZE_STRING);
            $propertiesWithSimpleKeys[$simpleKey] = $value;
        }

        return $propertiesWithSimpleKeys;
    }



    /**
     * given array of field names output parenthesied, comma separate list
     * e.g.
     * input:
     *      ['one', 'two', 'three']
     *
     * output
     *      '(one, two, three)'
     *
     * @param array $fields
     * @return string
     */
    public static function fieldListToInsertString($fields)
    {
        return ' (' . implode(', ', $fields) . ')';
    }

    /**
     * given array of field names output parenthesied, comma separate list, with colon prefix
     * e.g.
     * input:
     *      ['one', 'two', 'three']
     *
     * output
     *      'value (:one, :two, :three)'
     *
     * @param array $fields
     * @return string
     */
    public static function fieldListToValuesString($fields)
    {
        $formattedFields = [];
        foreach ($fields as $field) {
            $fieldWithColonPrefix = ':' . $field;
            $formattedFields[] = $fieldWithColonPrefix;
        }

        return ' value (' . implode(', ', $formattedFields) . ')';
    }

    /**
     * given array of field names output comma separate list, with equals-colon syntax
     * e.g.
     * input:
     *      ['a', 'b']
     *
     * output
     *      'a = :a, b = :b'
     *
     * @param array $fields
     * @return string
     */
    public static function fieldListToUpdateString($fields)
    {
        $formattedFields = [];
        foreach ($fields as $field) {
            $fieldWithEqualsColonPrefix = $field . ' = :' . $field;
            $formattedFields[] = $fieldWithEqualsColonPrefix;
        }

        return ' ' . implode(', ', $formattedFields) . ' ';
    }
}
