<?php
class DBC
{
    public static $connection;
    private static $host = "localhost";
    private static $user = "";
    private static $pwd = "";

    /**
     * Connect to a specific database
     * 
     * @param   string  $dbname  Takes the database name.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     */
    public static function connect($dbname)
    {
        self::$connection = new mysqli(self::$host, self::$user, self::$pwd, $dbname);
        if (self::$connection->connect_error) {
            die("Connection failed: " . self::$connection->connect_error);
        }
    }

    /**
     * Used for complex Querys
     * 
     * @param   string  $query  Takes a full sql query.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return mixed
     */
    public static function query($query)
    {
        return mysqli_query(self::$connection, $query);
    }

    /**
     * Close SQL connection
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return bool
     */
    public static function close()
    {
        return self::$connection->close();
    }

    public static function getInsertId(){
      return mysqli_insert_id(self::$connection);
    }

    /**
     * SQL insert array label, value pairs to table
     * 
     * @param   string  $table      Name of the table to which should be inserted
     * @param   array   $values     Column names as keys and values as values that should be inserted
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    public static function insert($table, $values)
    {
        $labelString = $valueString = "";
        foreach($values as $key => $value){
            $labelString .= (empty($labelString) ? "" : ", ") . $key;
            $valueString .= (empty($valueString) ? "" : ", ") . "'$value'";
        }
        $sql = "INSERT INTO $table ($labelString) VALUES ($valueString)";
        return self::buildQueryResult($sql);
    }

    /**
     * SQL update Table with array of label, value pairs
     * 
     * @param   string  $table      Name of the table to which should be updated
     * @param   array   $values     Column names as keys and values as values that should be updated
     * @param   array   $optional   Options like where, order by, limit in a key, value array. 
     * Key as option and value as the option value. 
     * The option 'where' could be a string or an array in which the first element 
     * is without a key and the following with keys which are used vor and, or statements.
     * 
     * Example:
     * 
     * array("where" => array("str='str'", "and" => "date='date'"))
     * array("where" => array("str", "LIKE" => "pattern"))
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    public static function update($table, $values, $optional = array())
    {
        $updateString = "";
        foreach($values as $key => $value){
            $updateString .= (empty($updateString) ? "" : ", ") . "$key='$value'";
        }
        $optionalString = self::buildOptionalString($optional);
        $sql = "UPDATE $table SET $updateString $optionalString";
        return self::buildQueryResult($sql);
    }

    /**
     * SQL delete entrys from table
     * 
     * @param   string  $table      Name of the table in which should entrys be deleted
     * @param   array   $optional   Options like where, order by, limit in a key, value array. 
     * Key as option and value as the option value. 
     * The option 'where' could be a string or an array in which the first element 
     * is without a key and the following with keys which are used vor and, or statements.
     * 
     * Example:
     * 
     * array("where" => array("str='str'", "and" => "date='date'"))
     * array("where" => array("str", "LIKE" => "pattern"))
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    public static function delete($table, $optional = array())
    {
        $optionalString = self::buildOptionalString($optional);
        $sql = "DELETE FROM $table $optionalString";
        return self::buildQueryResult($sql);
    }

    /**
     * SQL Select all from table
     * 
     * @param   string  $table      Name of the table to which should be selected
     * @param   array   $optional   Options like where, order by, limit in a key, value array. 
     * Key as option and value as the option value. 
     * The option 'where' could be a string or an array in which the first element 
     * is without a key and the following with keys which are used vor and, or statements.
     * 
     * Example:
     * 
     * array("where" => array("str='str'", "and" => "date='date'"))
     * array("where" => array("str", "LIKE" => "pattern"))
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mysqli_result
     */
    public static function select($table, $optional = array())
    {
        $optionalString = self::buildOptionalString($optional);
        $sql = "select * from $table $optionalString";
        return self::buildQueryResult($sql);
    }

    /**
     * Builds SQL string for optional parameters
     * 
     * @param   array   $optional   Takes Array of parameters.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    private static function buildOptionalString($optional)
    {
        $optionals = array();
        foreach ($optional as $key => $value) {
            $optionals = $optionals + self::buildKeywordString($key, $value);
        }
        $result = "";
        foreach ($optionals as $key => $value) {
            $result .= self::s($result) . "$key $value";
        }
        return $result;
    }

    /**
     * -This function is WIP!-
     * Builds SQL array for keywords
     * 
     * @param   string   $key   Takes key as keyword.
     * @param   string   $value Takes value as parameter value.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  array
     */
    private static function buildKeywordString($key, $value)
    {
        $result = array();
        switch (strtolower($key)) {
            case 'where':
                $result[$key] = self::buildWhereString($value);
                break;
            case 'order by':
                $result[$key] = self::buildOrderString($value);
                break;
            default:
                $result[$key] = $value;
                break;
        }
        return $result;
    }

    /**
     * Builds SQL string for where clause
     * 
     * @param   mixed   $where   Takes string or array to build the where clause.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    private static function buildWhereString($where)
    {
        if(!is_array($where)){
            return $where;
        } else {
            return self::buildWhereOperators($where);
        }
    }

    /**
     * Builds SQL string for where operators
     * 
     * @param   array   $where   Takes array to build the where operators.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    private static function buildWhereOperators($where)
    {
        $whereString = "";
        if (array_key_first($where) != 0)
            return self::throwException("First key in where array must not be defined or [0].");

        foreach ($where as $key => $value) {
            switch (strtolower($key)) {
                case '0':
                case 'and':
                case 'or':
                case 'not':
                case 'like':
                case 'not like':
                    $whereString .= self::buildBasicOperator($key, $value, $whereString);
                    break;
                default:
                    self::throwException("'$key' is not a valid operator.");
                    break;
            }
        }

        return $whereString;
    }

    /**
     * Builds SQL string for order by clause
     * 
     * @param   mixed   $order   Takes string or array to build the where clause.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    private static function buildOrderString($order)
    {
        if(!is_array($order)){
            return $order;
        } else {
            return self::buildOrderOperators($order);
        }
    }

    /**
     * Builds SQL string for multiple order by columns
     * 
     * @param   array   $order   Takes array to build the order by clause.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    private static function buildOrderOperators($order)
    {
        $orderString = "";
        foreach ($order as $key => $value) {
            if(is_numeric($key)){
                $orderString .= self::cS($orderString).self::buildOrderColumnString($value);
            } else {
                self::throwException("'$key' is not a valid key in 'order by' sequential array.");
            }
        }

        return $orderString;
    }

    /**
     * Builds SQL string for order by columns and keywords tuple.
     * 
     * @param   array   $columns   Takes array to build the order by keyword tuple.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    private static function buildOrderColumnString($columns)
    {
        $columnString = "";
        if(!is_array($columns)){
            $columnString = $columns;
        } else {
            if (!(array_keys($columns) !== range(0, count($columns) - 1)) && count($columns) < 3) {
                $columnString = $columns[0].self::s($columns[1]).$columns[1];
            } else {
                self::throwException("'order by' array could only have sequential arrays with maximum 2 entrys.");
            }
        }
        return $columnString;
    }

    /**
     * Builds SQL string for a basic operator pair
     * 
     * @param   string  $key   Takes key for the operator label.
     * @param   string  $value Takes value for the operator value.
     * @param   string  $first Takes the building string to check for spaces.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    private static function buildBasicOperator($key, $value, $first)
    {
        return self::s($first) . (is_numeric($key) ? "" : $key) . " $value";
    }

    /**
     * Checks if string is empty, otherwise add space
     * 
     * @param   string  $empty   Takes a string to check.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    private static function s($empty)
    {
        return (empty($empty) ? "" : " ");
    }

    /**
     * Checks if string is empty, otherwise add comma and space
     * 
     * @param   string  $empty   Takes a string to check.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    private static function cS($empty)
    {
        return (empty($empty) ? "" : ", ");
    }

    /**
     * Gets the latest SQL error
     * 
     * @param   string  $sql   Takes a string as SQL to print as comparison.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  string
     */
    public static function getErrorCode($sql = "")
    {
        return "Error: " . ($sql != "" ? "$sql<br>" : "") . mysqli_error(self::$connection);
    }

    /**
     * Sends a SQL to the DB and checks for errors
     * 
     * @param   string  $sql   Takes a string as SQL to send to DB.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  mixed
     */
    private static function buildQueryResult($sql)
    {
        $result = mysqli_query(self::$connection, $sql);
        if(!$result)
            self::throwException(self::getErrorCode($sql));
        return $result;
    }

    /**
     * Throws a Exception
     * 
     * @param   string  $e   Takes a string for the error code.
     * 
     * @author  Kimboo <k.pleiss@tuta.io>
     * @return  Exception
     */
    private static function throwException($e)
    {
        return throw new Exception($e);
    }
}
?>