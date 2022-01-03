# Changelog

All Notable changes to `pdo-crud-for-free-repositories` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT ?
not sure all seems to be working nicely ....

### Added
added reflection to infer SQL to create table from property types, if no SQL provided as constant or as argument to  DatabaseTableRepository::createTable()

so no need for constant like this in an entity class

```php
    class Movie
    {
        const CREATE_TABLE_SQL =
    <<<HERE
    CREATE TABLE IF NOT EXISTS movie (
        id integer PRIMARY KEY AUTO_INCREMENT,
        title text,
        price float,
        category text
    )
    HERE;
```

### Deprecated
solved deprecation warning
replaced FILTER_SANITIZE_STRING with trim() for variable $simpleKey
in class  Mattsmithdev\PdoCrudRepo\DatatbaseUtility::removeNamespacesFromKeys() - line 87

### Fixed
- updated README - to highlight no need for SQL create table constant any more

### Removed
- Nothing

### Security
- Nothing

### Faulty Towers
- I know nothing (but I learn, I learn !!)
