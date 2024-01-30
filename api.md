# Filemaker API

## End-point: Get Javascript
### Method: GET
>```
>/
>```

⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Get Databases
This endpoint makes an HTTP GET request to retrieve a list of databases from the specified URL. The response will be in JSON format with a status code of 200. However, the example response provided is an empty array.
### Method: GET
>```
>/databases
>```
### Headers

|Content-Type|Value|
|---|---|
|X-Authentication-Options|{"username": "",    "password": "" }|


### Response: 200
```json
[
    "Database 1",
    "Database 2",
    "Database 3",
    "Database 4",
    "Database 5",
    "Database 6",
    "Database 7"
]
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Get Database Layouts
This endpoint makes an HTTP GET request to retrieve the layouts for the "AMZ" database. The response will be in JSON format with an array of layout names.
### Method: GET
>```
>/databases/AMZ/layouts
>```
### Headers

|Content-Type|Value|
|---|---|
|X-Authentication-Options|{"username": "",    "password": "" }|


### Response: 200
```json
[
    "Table 1",
    "Table 2",
    "Table 3"
]
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Get Database Layouts Copy
This endpoint makes an HTTP GET request to retrieve the fields for a specific layout in the AMZ database. The response will be in JSON format with an array of field names.
### Method: GET
>```
>/databases/AMZ/layouts/Gen/fields
>```
### Headers

|Content-Type|Value|
|---|---|
|X-Authentication-Options|{"username": "",    "password": "" }|


### Response: 200
```json
[
    "Col 1",
    "Col 2",
    "Col 3"
]
```


⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃
