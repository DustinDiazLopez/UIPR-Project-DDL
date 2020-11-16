# UIPR CMS DDL

The **`app`** folder contains all the source and configuration files. In the releases you can download the xampp or 
lamp version to just drag and drop (and just do [Step 0](#step-0-configuring-php--mysql) & [Step 4](#step-4-creating-an-admin-user)).

## Prerequisites

- `PHP 5.5` or above (tested with `PHP 7.4`)
- `MySQL 5.6+`

Theoretically, it should work with `PHP5.5` or above, it is recommended to use `PHP5.6` or above.

***A VERSION LOWER THAN WHAT IS SPECIFIED MIGHT NOT WORK***.

### Expected file structure (XAMPP or LAMP)
```
xampp (or var)/
├── htdocs (or www)/
│   └── app-name/
│      ├── connect.php
│      └── ...   
└── ddl-config/
    └── ddl-config.json
```

## Installation Process

Please follow these steps ***carefully***.

### Step 0: Configuring PHP & MySQL

#### Installing a library for LAMP (not required for xampp)
```terminal
sudo apt-get install php7.4-mbstring
sudo apt-get install php7.4-dom
```
Replace `7.4` with your version of PHP

#### Recommended settings for `php.ini`

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


#### Restart Apache 

For `XAMPP` just click `start` and `stop` in the GUI, but for `LAMP` (or Linux):

```terminal
sudo systemctl restart apache2
```

### Step 1: The Config File
Create a folder in `xampp` or `var` called `ddl-config` (it ***HAS*** to be that name, unless 
you changed it in the source code), in that folder create a folder called `files`, and a JSON file called 
`ddl-config.json`. Then add this JSON object to the file:

```json
{ 
    "host": "localhost", 
    "port": "3306", 
    "username": "dustin", 
    "password": "password", 
    "database": "UIPRCMSDDL", 
    "salt": "exampleSalt"
}
```

Change `exampleSalt` to a silly string of characters, e.g., `JILASHdfjskfhalskjdjLKJFHsfhlkjsdjhld` (go crazy!), 
and change the rest of the information (`host`, `port`, `username`, `password`) to match your MySQL configuration.

#### WARNING!!!
Make sure this file ***IS NOT*** in a publicly available location. Make sure it is the root directory of xampp
(`xampp` folder), or lamp (`var` folder)

#### Socket?
***IF you need to specify a socket***, you'll have to edit `connect.php`, and include the socket in both functions of `connect.php`
(`connect_obj()`, `connect()`)

Please reference the [PHP documentation](https://www.php.net/manual/en/mysqli.construct.php).
> **socket** 
>
> Specifies the socket or named pipe that should be used.
>
> > ***Note:*** 
> >
> > Specifying the socket parameter will not explicitly determine the type of connection to be used when connecting to the MySQL server. How the connection is made to the MySQL database is determined by the host parameter.

For example:
```PHP
$config['socket'] = 'path/to/socket.sock';

/**
 * Establishes a connection to the database with the login information specified in {@link DDL_PATH} / {@link PATH_TO_CONFIG}
 * @return false|mysqli object which represents the connection to a MySQL Server or false if an error occurred.
 */
function connect() 
{
    global $config;
    return mysqli_connect($config['host'], $config['username'], $config['password'], $config['database'], $config['port'], $config['socket']);
}

/**
 * Establishes a connection to the database with the login information specified in {@link DDL_PATH} / {@link PATH_TO_CONFIG}
 * @return mysqli the {@link mysqli::__construct} object.
 */
function connect_obj()
{
    global $config;
    $mysqli = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port'], $config['socket']);
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    return $mysqli;
}
```

---

### Step 2: Setting up the Database
- Download the SQL script from the [latest release](https://github.com/DustinDiazLopez/UIPR-Project-DDL/releases).

    - Either insert or paste the `.sql` (inside the `ddl-config` folder) script in phpMyAdmin (refer to this 
    [link](https://stackoverflow.com/questions/13955988/insert-sql-file-into-your-mysql-database)),
    - or execute the following command (in the MySQL shell):
    ```SQL
    mysql> source path_to_sql
    ```

---

### Step 3: Copying over the source code
- In `htdocs` or `www` folder, create a new folder (e.g., `ddl-cms`), and copy over the files of the downloaded 
release, and try to access, e.g., ` http://localhost/ddl-cms/en/hello.php `.It should display the PHP information.

    - This step will require more setup on `LAMP` (see [this](https://unix.stackexchange.com/a/174114) for setting 
    write permissions).
        - Give permissions to the `ddl-config` folder, and to the `ddl-cms` folder

    - If you wish to use the `html` folder or any other folder, please change
the first line in `connect.php` (`ddl-cms/connect.php`) to match the new file structure.

    ```PHP
    define('DDL_PATH', './../../../ddl-config');
    ```

---

### Step 4: Creating an Admin User
- Access `hello.php` again.

- Now pass in as the argument `pwd` (e.g., `hello.php?pwd=your_password`). This will return the hashed version of the
inputted text, using the hash algorithm (by default `SHA-526`) specified in the config file of `step 1`. It should
return something like:

    ```SQL
    INSERT INTO `admin` (`email`, `username`, `password`) VALUES ('<email>', '<username>', '<hashed_password>');
    ```

- Once you've inserted the outputted SQL command, you should have access to the system (go to `login.php`).

---
