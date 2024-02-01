# FileMaker API PHP Library

This is a PHP client to interact with the FileMaker Data API. Use this client library to make your PHP application interact flawlessly with the FileMaker database.

# Table of Contents

- **[FileMaker API PHP Library](#filemaker-api-php-library)**
    - [Getting Started](#getting-started)
    - [Instantiate the FileMaker Object](#instantiate-the-filemaker-object)
    - [Fetching Records](#fetching-records)
    - [Searching Records](#searching-records)
    - [Adding Records](#adding-records)
    - [Updating Records](#updating-records)
    - [Deleting Records](#deleting-records)
    - [Errors](#errors)

- **[FileMaker Memory](#filemaker-memory)**
    - [Configuration](#configuration)
    - [Initialization](#initialization)
    - [Storing a Token](#storing-a-token)
    - [Retrieving a Token](#retrieving-a-token)
    - [Checking if a Database Has a Stored Token](#checking-if-a-database-has-a-stored-token)
    - [Deleting a Token](#deleting-a-token)
    - [Listing all Databases with Saved Tokens](#listing-all-databases-with-saved-tokens)
    - [Notes](#notes)

## Getting Started

First, you need to include the library and use it in your PHP file. Make sure the library file path is correct.

```php
<?php
require_once 'path/to/FileMaker.php';
use Filemaker\FileMaker;
```

## Instantiate the FileMaker Object

Use your database credentials to create a new `FileMaker` object:

```php
<?php
$fileMaker = new FileMaker('yourUsername', 'yourPassword', 'yourDatabaseName', 'yourTableName');
```

## Fetching Records

To fetch records from the database, we use the `getRecords` method:

```php
<?php
$records = $fileMaker->getRecords();
```

## Searching Records

To search for specific records in the database, you can use the `search` method:

```php
<?php
$searchResults = $fileMaker->search("query");
```

## Adding Records

records can be added by calling the `addRecord` method with an array of field-data pairs:

```php
<?php
$response = $fileMaker->addRecord([
    'field1' => 'data1',
    'field2' => 'data2',
]);
```

## Updating Records

To update record data, simply call the `updateRecord` method with the record Id and the new data:

```php
<?php
$response = $fileMaker->updateRecord(1, [
    'field1' => 'newData1',
    'field2' => 'newData2',
]);
```

## Deleting Records

You can delete a record by calling the `deleteRecord` method with the Id of the record you wish to delete as an argument:

```php
<?php
$response = $fileMaker->deleteRecord(1);
```

Note: These examples assume that you've instantiated your FileMaker object as `$fileMaker`.

## Errors

In case of an error, most methods will return an empty array. Always include error handling routines in your code when working with this library.

# Filemaker Memory

This library provides an application-wide place to store and manage database tokens. It exposes five critical functionalities which are initializing the memory space, storing, retrieving, checking, and deleting tokens tied to a particular database. Additionally, it also provides a way to list all databases that have saved tokens.

## Configuration

Make a copy of the [example.env](/example.env) file and rename it to `.env`. Then, fill in the required values.  
Here's an example:
```ini
DB_HOST=localhost
DB_DATABASE=apps
DB_USERNAME=root
DB_PASSWORD=password123
```


## Initialization

Before any operation, ensure that the memory space is available by initializing it:

```php
use Filemaker\FilemakerMemory;
FilemakerMemory::init();
```

## Storing a token

The method `save($database, $token)` is used to store a token for a particular database.

```php
FilemakerMemory::save($databaseName, $token);
```

`$databaseName` - The name of the database for which the token is being stored.

`$token` - The token that is to be stored.

## Retrieving a token

The method `get($database)` is used to get a token for a particular database.

```php
$token = FilemakerMemory::get($databaseName);
```

`$databaseName` - The name of the database whose token you are retrieving.

This returns the token if it was previously stored or null if the token does not exist.

### Checking if a database has a stored token

To check if a token exists for a particular database without retrieving it, use the `has($database)` method:

```php
$hasToken = FilemakerMemory::has($databaseName);
```

`$databaseName` - The name of the database whose token you are checking for.

## Deleting a token

To delete a token for a particular database, use the `delete($database)`
method:

```php
FilemakerMemory::delete($databaseName);
```

`$databaseName` - The name of the database whose token you are deleting.

## Listing all databases with saved tokens

To retrieve a list of all databases that have stored tokens, use the `list()` method:

```php
$databases = FilemakerMemory::list();
```

This will return an array of database names.

## Notes

This library relies on connection configuration defined in the `.env` file in your project. A generic connection setup is included in the example code. However, the actual connection implementation might vary depending on your use case and environment configuration. Make sure you have proper database connections set up before using this library.
