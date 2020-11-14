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

#### Recommened settings for `my.ini` (or `mysqldump.cnf` on LAMP)
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
```SQL
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

    - Either insert or paste the `.sql` script in phpMyAdmin (refer to this [link](https://stackoverflow.com/questions/13955988/insert-sql-file-into-your-mysql-database)),
    - or execute the following command (in the MySQL shell):
    ```SQL
    mysql> source path_to_sql
    ```

---

### Step 3: Copying over the source code
- Download the [latest release](https://github.com/DustinDiazLopez/UIPR-Project-DDL/releases).
- In `htdocs` or `www` folder, create a new folder (e.g., `uipr-colon`), and copy over the files of the downloaded 
release, and try to access, e.g., ` http://localhost/uipr-colon/hello.php `.It should display the PHP information.

    - This step might require more setup on `LAMP` (see [this](https://unix.stackexchange.com/a/174114) for setting 
    write permissions).

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
