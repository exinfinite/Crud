# crud by doctrine dbal

### 安裝

先在composer.json中增加

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/exinfinite/Crud.git"
    }
]
```

執行安裝

```php
composer require exinfinite/crud
```

### 套件初始化

```php
use Exinfinite\Crud;
$connectionParams = [
    'dbname' => [db name],
    'user' => [user],
    'password' => [password],
    'host' => [host],
    'driver' => 'pdo_mysql',
    'charset' => 'utf8mb4',
];
$crud = new DBALCrud(\Doctrine\DBAL\DriverManager::getConnection($connectionParams));
```

### 使用方式

```php
// 1.Doctrine dbal
$dbal = $crud->queryBuilder();
$dbal->select('*')->from([table_name])->execute()->fetchAll();

// 2.內建函式
$crud->selectAll([table_name]);
```
