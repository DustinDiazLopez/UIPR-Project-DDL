# UIPR CMS DDL

The **XAMPP_SERVER_FILES** folder contains all the PHP, HTML, CSS, and files.

## Expected file structure
```
xampp/
├── htdocs/
│   ├── uipr-colon/
│   │   └── ...
└── mysql_uiprcmsddl_config.json
```
The mysql configuration (`mysql_uiprcmsddl_config.json`) must be two directories back the 
`uipr-colon` folder. If this is not possible edit the `connect.php` (`uipr-colon/connect.php`) 
file to match the desired file structured.

## Requirements

- PHP 7.4
- MySQL 5.6.49

## Download the SQL script

[Download SQL](https://github.com/DustinDiazLopez/UIPR-Project-DDL/blob/main/XAMPP_SERVER_FILES/uiprcmsddl.sql)

### Insert .SQL file in phpMyAdmin
> https://stackoverflow.com/questions/13955988/insert-sql-file-into-your-mysql-database
