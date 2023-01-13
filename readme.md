# PHP Database Controller

Hi! At first, the Database Controller (DBC) is not a finished project but it has functionality for all SQL statements.
This object is designed to save time, work efficient and to combine SQL better with PHP. I continue to work on this project to make it better.

# Files you need

The only file you will need to include the DBC is the "**DBController.php**".

# Documentation
## Change login data for your Database

In the **DBController.php** there are class variables that you can change:
```PHP
private  static  $host = "localhost";
private  static  $user = "yourUsername";
private  static  $pwd = "yourPassword";
```

## How to include the DBC

```PHP
include_once 'path/to/your/DBController.php';
```

## Connect to a Database

```PHP
DBC::connect("databasename");'
```

and don't forget to close the connection when its not needed anymore:

```PHP
DBC::close();
```

## Insert to a table

You are inserting your columns by opening an array. The key as the column name and the value ... well as the value you like to insert.

```PHP
DBC::insert("tablename", ["column1" => "value1", "column2" => "value2"]);
```

## Update a table

You are updating your tables by opening an array to specify the columns and values you wish to change. The key as the column name and the value as your value as before with the insert.

```PHP
DBC::update("tablename", ["column1" => "value1"]);
```

The above would update all your table rows. You can specify the update method by adding an array for the optional parameters.
```PHP
DBC::update("tablename", ["column1" => "value1"], ["where" => "id=5"]);
```

Now it will only change the value for the row with the id of 5.

## Delete from a table

Deleting is also simple by using the delete method. The following would delete everything from a table.

```PHP
DBC::delete("tablename");
```

And with an optional parameter it would delete just a specific row.

```PHP
DBC::delete("tablename", ["where" => "id=10"]);
```

## Select from a table

The select is a little more complex because of the options you have with this query. The following selects all from the table.

```PHP
DBC::select("tablename");
```

To use the data you can use a while or a foreach as you like:

```PHP
// select with foreach
$result = DBC::select("tablename");
foreach ($result as $row) {
	print '<pre>';
	print_r($row);
	print '</pre>';
}

// select with while
$result = DBC::select("tablename");
while ($row = mysqli_fetch_assoc($result)) {
	print '<pre>';
	print_r($row);
	print '</pre>';
}
```

The select method works also with the optional parameter array. 

```PHP
$result = DBC::select("tablename", ["where" => "column1='value1'"]);
```

## More complex examples

The where clause can be expanded by another array to add multiple conditions. The key represents the operator in between.
> **Important:** The first element of the where array has to have either no key or the key [0]

```PHP
DBC::select("DBController", ["where" => ["column1='value1'", "and" => "column2='value2'"]]);
```

In addition the where clause isn't the only phrase that works in this way.

```PHP
DBC::select("DBController", ["where" => ["column1='value1'"], "order by" => "column2", "limit" => 2]);
```

Also there is a method to write own way more complex SQL lines.

```PHP
DBC::query("select * from tablename");
```

Additional methods to debug:

```PHP
// Gets the last insert id
DBC::getInsertId();

// Gets the last error code (optional add SQL as string argument for better comparison)
DBC::getErrorCode();
```