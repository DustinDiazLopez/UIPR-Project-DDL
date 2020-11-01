# UIPR CMS DDL

The **`xampp`** folder contains all the PHP, HTML, and CSS files.

## Written With

- PHP 7.4
- MySQL 5.6.49

## Expected file structure (XAMPP/LAMP)
```
xampp (or var)/
├── htdocs (or www)/
│   └── uipr-colon/
│      ├── connect.php
│      └── ...   
└── colon-uipr-cms-ddl-files-and-config
    └── mysql_uiprcmsddl_config.json
```

You can run everything once, and it'll generate the config folder (w/ the sample json file), if the system allows it.

The mysql configuration (`mysql_uiprcmsddl_config.json`) must be two directories back the
`uipr-colon` folder and inside the `colon-uipr-cms-ddl-files-and-config` folder. If this is not possible edit the first
line in `connect.php` (`uipr-colon/connect.php`) file to match the desired file structured.


### The fist line in `connect.php`
```PHP
define('DDL_PATH', '../../colon-uipr-cms-ddl-files-and-config');
```

### mysql_uiprcmsddl_config.json example
```json
{ 
    "host": "localhost", 
    "port": "3306", 
    "username": "root", 
    "password": "password", 
    "database": "UIPRCMSDDL", 
    "salt": "$6$rounds=5000$exampleSalt$"
}
```

## Download the SQL script

[Download SQL](https://github.com/DustinDiazLopez/UIPR-Project-DDL/blob/main/xampp/colon-uipr-cms-ddl-files-and-config/uiprcmsddl.sql), and 
for inserting the `.sql` script refer to this
[link](https://stackoverflow.com/questions/13955988/insert-sql-file-into-your-mysql-database)
