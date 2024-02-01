# Filemaker Library API Documentation

This is a basic documentation on how to use the Filemaker JavaScript library.
- [Classes](#classes)
    - [FilemakerRecord](#filemakerrecord)
        - [constructor()](#constructor)
        - [static fromJSON(json)](#static-fromjsonjson)
    - [Filemaker](#filemaker)
        - [constructor(url, username, password, database, layout)](#constructorurl-username-password-database-layout)
        - [withUrl(url)](#withurlurl)
        - [withUsername(username)](#withusernameusername)
        - [withPassword(password)](#withpasswordpassword)
        - [withDatabase(database)](#withdatabasedatabase)
        - [withLayout(layout)](#withlayoutlayout)
        - [getActiveSessions()](#getactivesessions)
        - [search(query)](#searchquery)
        - [getDatabases()](#getdatabases)
        - [getLayouts()](#getlayouts)
        - [getRecords(limit, offset)](#getrecordslimit-offset)
        - [getRecord(id)](#getrecordid)
        - [getRows()](#getrows)
        - [updateRecord(id, record, addIfMissing)](#updaterecordid-record-addifmissing)
        - [deleteRecord(id)](#deleterecordid)
        - [deleteAllRecords()](#deleteallrecords)
        - [addRecord(record)](#addrecordrecord)
- [Note](#note)
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

