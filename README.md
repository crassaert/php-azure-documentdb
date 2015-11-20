# php-azure-documentdb
PHP wrapper for DocumentDB operations (beta version)

# Usage

## Install
`composer require crassaert/php-azure-documentdb`

## Instantiate
`$db = new AzureDocumentDB(AZURE_HOST, AZURE_KEY, false);`

## Databases operation

### List
`$db->get('database')->_list();`

### Creation
`$db->get('database')->create('my_database');`

### Selection
`$db->get('database')->select('my_database');`

### Remove

`$db->get('database')->delete('my_database');`

## Collections

Before requesting, you have to select a database (see previous paragraph).

### List
`$db->get('collection')->_list();`

### Creation
`$db->get('collection')->create('my_collection');`

### Selection
`$db->get('collection')->select('my_collection');`

### Remove

`$db->get('collection')->delete('my_collection');`

## Document

Before requesting, you have to select a database and a collection (see previous paragraph).

### Creation
`$db->get('document')->create($json);`

### Requesting

Fell free to write your SQL query here.

`$db->get('document')->query('SELECT * FROM my_table');`

### Remove

You have to select your document before removing it to obtain the internal ID.

```
$rid = $document->_rid;
$db->get('document')->delete($rid);

```

## TODO

Implement permissions, triggers, users, sprocs and UDF.

You can find all features on [Microsoft Azure Website](https://msdn.microsoft.com/fr-fr/library/azure/dn781481.aspx)