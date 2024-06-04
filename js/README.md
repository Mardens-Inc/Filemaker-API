# Filemaker-JS-Library

Filemaker-JS-Library is a powerful, robust, and easy-to-use library to interact with Filemaker databases using Filemaker Data API.

## Table of Contents
- [Installation](#installation)
- [Initialization](#initialization)
- [Instance Methods](#instance-methods)
  - [Setting URL](#1-setting-url)
  - [Setting Username](#2-setting-username)
  - [Setting Password](#3-setting-password)
  - [Setting Database](#4-setting-database)
  - [Setting Layout](#5-setting-layout)
  - [Fetching Active Sessions](#6-fetching-active-sessions)
  - [Searching Records](#7-searching-records)
  - [Getting Database List](#8-getting-database-list)
  - [Retrieving Layouts](#9-retrieving-layouts)
  - [Fetching Records](#10-fetching-records)
  - [Getting a Single Record](#11-getting-a-single-record)
  - [Retrieving Rows from a Layout](#12-retrieving-rows-from-a-layout)
  - [Deleting a Record](#13-deleting-a-record)
  - [Updating a Record](#14-updating-a-record)
  - [Adding a Record](#15-adding-a-record)
- [Error Handling](#error-handling)



## Installation

Include an installation step here according to your package manager.

## Initialization

```javascript
import Filemaker from 'Filemaker-JS-Library';
// or
import Filemaker from "https://cdn.jsdelivr.net/gh/Mardens-Inc/Filemaker-API/js/Filemaker.js";
const filemaker = new Filemaker('url', 'username', 'password', 'database', 'layout');
```

## Instance Methods

### 1. Setting URL
```javascript
filemaker.withUrl('newUrl');
```

### 2. Setting Username
```javascript
filemaker.withUsername('newUsername');
```

### 3. Setting Password
```javascript
filemaker.withPassword('newPassword');
```

### 4. Setting Database
```javascript
filemaker.withDatabase('newDatabase');
```

### 5. Setting Layout
```javascript
filemaker.withLayout('newLayout');
```

### 6. Fetching Active Sessions
```javascript
filemaker.getActiveSessions();
```

### 7. Searching Records
```javascript
filemaker.search('query');
```

### 8. Getting Database List
```javascript
filemaker.getDatabases();
```

### 9. Retrieving Layouts
```javascript
filemaker.getLayouts();
```

### 10. Fetching Records

```javascript
filemaker.getRecords();
```

Use this method to retrieve records from the FileMaker database. It returns a Promise object representing the array of records. Be sure to use await keyword or then method to get the result.

### 11. Getting a Single Record

```javascript
filemaker.getRecord('recordId');
```

Use this method to retrieve a single record with the given recordId from the FileMaker database. It returns a Promise object representing the record. Be sure to use await keyword or then method to get the result.

### 12. Retrieving Rows from a Layout

```javascript
filemaker.getRows('layout');
```

This method fetches rows from a given layout. It returns a Promise object representing the array of rows. Be sure to use await keyword or then method to fetch the rows.

### 13. Deleting a Record

```javascript
filemaker.delete('recordId');
```

Use this method to delete a record with a given recordId. It returns a Promise object that resolves to the status of the operation. Be sure to use await keyword or then method to perform the operation.

### 14. Updating a Record

```javascript
filemaker.update('recordId', {fieldToUpdate: 'newValue'});
```

This method updates a record with the given recordId using the provided update fields. It returns a Promise object representing the status of the operation. Be sure to use await keyword or then method to perform the operation.

### 15. Adding a Record

```javascript
filemaker.addRecord({fieldName: 'fieldValue'});
```

## Error Handling

This library throws errors when required fields are not set or network operations fail. Make sure to use try/catch blocks.

```javascript
try {
    const records = await filemaker.search('query');
} catch(error) {
    console.log(error.message);
}
```
