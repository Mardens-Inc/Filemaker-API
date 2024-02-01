# Filemaker Library API Documentation

This is a basic documentation on how to use the Filemaker JavaScript library.
- [Installation](#installation)
    - [Easy (Recommended)](#easy-recommended)
    - [Manual (CORS)](#manual-cors)
- [Classes](#classes)
    - [FilemakerRecord](#filemakerrecord)
        - [constructor()](#constructor-filemakerrecord)
        - [static fromJSON(json)](#static-fromjsonjson-filemakerrecord)
    - [Filemaker](#filemaker)
        - [constructor(url, username, password, database, layout)](#constructorurl-username-password-database-layout-filemaker)
        - [withUrl(url)](#withurlurl-filemaker)
        - [withUsername(username)](#withusernameusername-filemaker)
        - [withPassword(password)](#withpasswordpassword-filemaker)
        - [withDatabase(database)](#withdatabasedatabase-filemaker)
        - [withLayout(layout)](#withlayoutlayout-filemaker)
        - [getActiveSessions()](#getactivesessions-filemaker)
        - [search(query)](#searchquery-filemaker)
        - [getDatabases()](#getdatabases-filemaker)
        - [getLayouts()](#getlayouts-filemaker)
        - [getRecords(limit, offset)](#getrecordslimit-offset-filemaker)
        - [getRecord(id)](#getrecordid-filemaker)
        - [getRows()](#getrows-filemaker)
        - [updateRecord(id, record, addIfMissing)](#updaterecordid-record-addifmissing-filemaker)
        - [deleteRecord(id)](#deleterecordid-filemaker)
        - [deleteAllRecords()](#deleteallrecords-filemaker)
        - [addRecord(record)](#addrecordrecord-filemaker)
- [Note](#note)
- [Example Usage](#example-usage)
    - [Instance Creation](#instance-creation)
    - [Configuring Parameter](#configuring-parameter)
    - [API Calls](#api-calls)

# Installation
Follow the webserver installation on the [main README](README.MD) then include the following script in your HTML file.


## Easy (Recommended)
```html
<script type="module" src="script.js"></script>
```
then add this to your `script.js` file:

```javascript
import { Filemaker, FilemakerRecords } from 'https://filemaker-api-server.local/';
```

## Manual (CORS)
This is only recommended if you are unable to use the easy method due to CORS restrictions and are unable to bypass them.

Add this to the top of your index.php file:
```php
<?php

/**
 * Fetches libraries from the Marden's website and saves it to the server.
 * This is done to fix the issue of the libraries being blocked by CORS.
 * This script will be called everytime the page is loaded.
 * Is that a good Idea? Probably not. But it works.
 */

$libs = [
    "filemaker.js" => "https://filemaker-api-server.local/",
    //... any other libraries
];

foreach ($libs as $file => $url) {
    $file = $_SERVER['DOCUMENT_ROOT'] . "/assets/lib/$file";

    // Get last modified time of the url
    // Ignore SSL errors
    $lastModifiedUrl = constructUrl($url, "time");
    $last_modified = retrieveUrlContents($lastModifiedUrl, "text/plaintext");
    $last_modified = intval($last_modified);

    if (!file_exists($file) || $last_modified >= filemtime($file)) {
        // Download the file
        $fileUrl = constructUrl($url);
        file_put_contents($file, retrieveUrlContents($fileUrl, "text/javascript"));
    }
}

/**
 * Retrieves the contents of a URL using the given URL and header content type.
 *
 * @param string $url The URL to retrieve the contents from.
 * @param string $headerContentType The header content type to be set in the request.
 *
 * @return bool|string The retrieved contents of the URL as a string, or false if unsuccessful.
 */
function retrieveUrlContents(string $url, string $headerContentType): bool|string
{
    return file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Content-Type: $headerContentType"
        ], 'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]));
}

/**
 * Constructs a URL using the given base URL and endpoint.
 *
 * @param string $baseUrl The base URL of the website.
 * @param string|null $endpoint The optional endpoint to append to the base URL. Default is null.
 *
 * @return string The constructed URL string.
 */
function constructUrl(string $baseUrl, string $endpoint = null): string
{
    return $endpoint ? $baseUrl . "/?{$endpoint}" : $baseUrl . "/";
}

?>
```

# Classes

## `FilemakerRecord`

### `constructor()`

- Instantiates a new FilemakerRecord

### `static fromJSON(json)`

- Creates a new FilemakerRecord from a JSON object
- Argument:
    - `json`: An object representing the FilemakerRecord

## `Filemaker`
### `constructor(url, username, password, database, layout)`

- Instantiates a new Filemaker object
- Arguments:
    - `url`: The URL of the Filemaker server
    - `username`: The username to use to connect to the server
    - `password`: The password to use to connect to the server
    - `database`: The database to use
    - `layout`: The layout/table to use


### `withUrl(url)`

- Sets the server URL for the instance
- Argument:
    - `url`: The URL of the server to be set

### `withUsername(username)`

- Sets the username for the instance
- Argument:
    - `username`: The username to be set

### `withPassword(password)`

- Sets the password for the instance
- Argument:
    - `password`: The password to be set

### `withDatabase(database)`

- Sets the database for the instance
- Argument:
    - `database`: The name of the database to be set

### `withLayout(layout)`

- Sets the layout for the instance
- Argument:
    - `layout`: The layout to be set for the instance

### `getActiveSessions()`

- Fetches a list of databases with active sessions
- Returns a Promise that resolves with a JSON object representing array of active sessions

### `search(query)`

- Searches the database for records matching the query
- Argument:
    - `query`: The query string to search for
- Returns a Promise that resolves with an array of `FilemakerRecord` objects that match the query

### `getDatabases()`
- Fetches a list of databases from the server
- Returns a Promise that resolves with a JSON object representing an array of databases

### `getLayouts()`
- Fetches a list of layouts from the specified database
- Returns a Promise that resolves with a JSON object representing an array of layouts

### `getRecords(limit, offset)`

- Fetches records from Filemaker database
- Arguments:
    - `limit`: The maximum number of records to fetch. Default is 10
    - `offset`: The offset position to start fetching records. Default is 0
- Returns a Promise that resolves with an array of `FilemakerRecord` objects

### `getRecord(id)`

- Retrieves a record with the specified ID
- Argument:
    - `id`: The ID of the record to retrieve
- Returns a Promise that resolves with the retrieved `FilemakerRecord`

### `getRows()`
- Fetches rows from the specified database and layout
- Returns a Promise that resolves with a JSON object representing an array of rows

### `updateRecord(id, record, addIfMissing)`

- Updates a record in the database
- Arguments:
    - `id`: The ID of the record to update
    - `record`: The updated record data
    - `addIfMissing`: If true, adds the record if it does not already exist. Default is false
- Returns a Promise that resolves with the updated `FilemakerRecord`

### `deleteRecord(id)`

- Deletes a record with the specified ID
- Argument:
    - `id`: The ID of the record to delete

### `deleteAllRecords()`

- Deletes all records from the specified layout
- Returns a void Promise that resolves when the deletion is successful

### `addRecord(record)`

- Adds a record to the database
- Argument:
    - `record`: The `FilemakerRecord` to add
- Returns a Promise that resolves with the added `FilemakerRecord`

# Note

All methods are `async` and may throw errors. Always handle rejections when using these methods.


# Example Usage

## Instance Creation

Creating an instance of class `Filemaker` and `FilemakerRecord`:

```javascript
let record = new FilemakerRecord();

const fmk = new Filemaker(url, username, password, database, layout);
```

## Configuring Parameter

Setting URL, username, password, database, and layout to the object:

```javascript
fmk.withUrl(url);
fmk.withUsername(username);
fmk.withPassword(password);
fmk.withDatabase(database);
fmk.withLayout(layout);
```

## API Calls

Various API calls you can make with the `Filemaker` and `FilemakerRecord` class:

Fetching Active Sessions:

```javascript
let activeSessions = await fmk.getActiveSessions();
```

Search the database:

```javascript
let searchResults = await fmk.search(query);
```

Retrieve databases:

```javascript
let dbList = await fmk.getDatabases();
```

Get layouts:

```javascript
let layouts = await fmk.getLayouts();
```

Fetch records:

```javascript
let records = await fmk.getRecords(limit, offset);
```

Fetching a specific record by ID:

```javascript
let record = await fmk.getRecord(id);
```

Get rows:

```javascript
let rows = await fmk.getRows();
```

Update (and possibly add) a record:

```javascript
let updatedRecord = await fmk.updateRecord(id, record, addIfMissing);
```

Delete a specific record:

```javascript
await fmk.deleteRecord(id);
```

Delete all records:

```javascript
await fmk.deleteAllRecords();
```

Adding a record:

```javascript
let newRecord = await fmk.addRecord(record);
```

Please note that all of these examples are asynchronous operations. Remember to handle promises correctly to avoid unhandled promise rejection warnings or errors.


