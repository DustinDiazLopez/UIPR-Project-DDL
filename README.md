# UIPR CMS DDL

The **`xampp`** folder contains all the PHP, HTML, and CSS files.

## Prerequisites

- `PHP 5.5` or above (tested with `PHP 7.4`)
- `MySQL 5.6+`

Theoretically, it should work with `PHP5.5` or above, it is recommended to use `PHP5.6` or above.

***A VERSION LOWER THAN WHAT IS SPECIFIED MIGHT NOT WORK***.

### Expected file structure (XAMPP or LAMP)
```
xampp (or var)/
├── htdocs (or www)/
│   └── uipr-colon/
│      ├── connect.php
│      └── ...   
└── colon-uipr-cms-ddl-files-and-config
    └── mysql_uiprcmsddl_config.json
```

## Installation Process

Please follow these steps ***carefully***.

### Step 0: Configuring PHP & MySQL

Consider modifying the `php.ini` and the `my.ini` (`mysqldump.cnf` in LAMP) file to be more flexible.

#### Recommened settings for `php.ini`

```ini
; This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser.
max_execution_time = 120

; This sets the maximum time in seconds a script is allowed to parse input data, like POST and GET.
max_input_time = 120

; Do note that memory_limit should be larger than post_max_size
memory_limit = 512M

; post_max_size value must be larger than upload_max_filesize, in order to, upload large files.
post_max_size = 256M

; The maximum size of an uploaded file.
upload_max_filesize = 256M

; The maximum number of files allowed to be uploaded simultaneously.
; In the application it is hardcoded to be 100
max_file_uploads = 25
```

#### Recommened settings for `my.ini` (or `mysqldump.cnf`)
```ini
# the same value as upload_max_filesize in the php.ini file
max_allowed_packet = 256M
```

#### Restart the services

For `XAMPP` just click `start` and `stop` in the GUI, but for `LAMP` (or Linux):

MySQL:
```terminal
sudo systemctl restart mysql
```
Apache
```terminal
sudo systemctl restart apache2
```

##### Set `max_allowed_packet` globally for Linux:
This will set it to 1GB, but MySQL will only use what it needs.
```terminal
mysql> SET GLOBAL max_allowed_packet=1073741824;
```
***Do note*** this is only temporary, and ***will*** be resseted after a restart (of any kind).
### Step 1: The Config File
Create a folder in `xampp` or `var` called `colon-uipr-cms-ddl-files-and-config` (it ***HAS*** to be that name, unless 
you changed it in the source code),
and in that folder create a JSON file called `mysql_uiprcmsddl_config.json`. Then add this JSON object to the file:
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

Change `exampleSalt` to a silly string of characters (go crazy!), and change the rest of the information to match your 
MySQL configuration.

#### WARNING!!!
Make sure this file ***IS NOT*** in a publicly available location. Make sure it is the root directory of xampp
(`xampp` folder), or lamp (`var` folder)

#### Salt
The application will use `SHA-512` for hashing the passwords. If you wish to change this (not recommended) follow this
[link](https://www.php.net/manual/en/function.crypt.php)

---

### Step 2: Setting up the Database
- [Download the SQL script](https://github.com/DustinDiazLopez/UIPR-Project-DDL/blob/main/xampp/colon-uipr-cms-ddl-files-and-config/uiprcmsddl.sql).

    - Either insert or paste the `.sql` script in phpMyAdmin (refer to this [link](https://stackoverflow.com/questions/13955988/insert-sql-file-into-your-mysql-database)),
    - or execute the following command (in the MySQL shell):
    ```MySQL
    mysql> source path_to_sql
    ```

---

### Step 3: Copying over the source code

- In `htdocs` or `www` folder, copy over the `uipr-colon` folder, and try to access, e.g.,
 ` http://localhost/uipr-colon/hello.php `.It should display the PHP information.

    - This step might require more setup on `LAMP`.

    - If you wish to use the `html` folder or any other folder, please change
the first line in `connect.php` (`uipr-colon/connect.php`) to match the new file structure.

    ```PHP
    define('DDL_PATH', '../../colon-uipr-cms-ddl-files-and-config');
    ```

---

### Step 4: Creating an Admin User
- Access `hello.php` again.

- Now pass in as the argument `pwd` (e.g., `hello.php?pwd=your_password`). This will return the hashed version of the
inputted text, using the hash algorithm (by default `SHA-526`) specified in the config file of `step 1`. It should
return something like:
    ```text
    r4nwUWpjef1wJgwfW4WgSim2P0qskuBFmYQ/p56LZDONtVZiS6CHNBji25G9CTc/kOAjkvwnxeJw4Wr8CuTjS0
    ```

- Now use the following SQL command, replacing the `<hashed_password>` with whatever you got back, and then finally, 
execute the SQL command.

    ```SQL
    INSERT INTO `admin` (`email`, `username`, `password`) VALUES ('example@example.com', 'username', '<hashed_password>');
    ```

- You should now have access to the system (go to `login.php`).

---
