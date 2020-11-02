<?php
// this file is included in utils.php

// in add.php contains the inserts methods

//insert file
define('SQL_INSERT_FILE_CONTENT', 'INSERT INTO `file` (`content`) VALUES (?);');
// get all
define('SQL_ALL_ITEMS', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.create_at, i.published_date, i.year_only FROM item i INNER JOIN `type` t ON i.type_id = t.id ');
define('SQL_GET_AUTHORS', "SELECT id, author_name FROM author");
define('SQL_GET_SUBJECTS', 'SELECT `id`, `subject` FROM `subject`');
define('SQL_GET_ADMINS', 'SELECT id, email, username FROM `admin`');
define('GET_FILES_ITEM_ID', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.create_at, i.published_date, i.year_only FROM `file` f inner join file_has_item fi ON fi.file_id = f.id inner join item i on fi.item_id = i.id INNER JOIN `type` t ON i.type_id = t.id ');
define('SQL_GET_DOC_TYPES', 'SELECT `id`, `type` FROM `type`');

// get by id
define('SQL_GET_FILES', 'SELECT fi.item_id, f.id, f.`path`, f.`content`, f.`filename`, f.`type`, f.`size` FROM `file` f inner join file_has_item fi inner join item i on i.id = fi.item_id AND fi.file_id = f.id WHERE i.id = ');
define('SQL_GET_IMAGE', 'SELECT image FROM image where id = ');
define('SQL_GET_FILE', 'SELECT `id`, `content`, `filename`, `type`, `size`, `path` FROM `file` WHERE `id` = ');
define('SQL_GET_TYPE', 'SELECT `id`, `type` FROM `type` WHERE `id` = ');
define('SQL_GET_SUBJECTS_BY_ID', 'SELECT s.`subject` FROM `subject` s inner join item_has_subject `is` inner join item i on i.id = `is`.item_id AND `is`.subject_id = s.id WHERE i.id = ');
define('SQL_GET_AUTHORS_BY_ID', "SELECT a.author_name FROM author a inner join author_has_item ai inner join item i on i.id = ai.item_id AND ai.author_id = a.id WHERE i.id = ");
define('SQL_GET_ITEMS_BY_AUTHOR_ID', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.meta, i.create_at, i.published_date, i.year_only FROM author_has_item ai INNER JOIN author a INNER JOIN item i INNER JOIN `type` t ON i.type_id = t.id WHERE a.id = author_id AND i.id = item_id AND a.id = ');
define('SQL_GET_ITEMS_BY_SUBJECT_ID', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.meta, i.create_at, i.published_date, i.year_only FROM item_has_subject ai INNER JOIN `subject` a INNER JOIN item i INNER JOIN `type` t ON i.type_id = t.id WHERE a.id = subject_id AND i.id = item_id AND a.id = ');
define('SQL_GET_ITEMS_BY_TYPE_ID', 'SELECT i.id, i.title, t.`type`, i.image_id, i.description, i.meta, i.create_at, i.published_date, i.year_only FROM item i INNER JOIN `type` t ON i.type_id = t.id AND i.type_id = ');
define('SQL_GET_PWD_BY_ID', "SELECT `password` FROM `admin` WHERE `id` = ");

define('SQL_GET_TYPE_BY_ID', "SELECT `id`, `type` FROM `type` WHERE `id` = ");
define('SQL_GET_SUBJECT_BY_ID', "SELECT `id`, `subject` FROM `subject` WHERE `id` = ");
define('SQL_GET_AUTHOR_BY_ID', "SELECT `id`, `author_name` FROM `author` WHERE `id` = ");

// get admin by
define('SQL_GET_ADMIN_BY_ID', 'SELECT `id`, `email`, `username`, `password` FROM `admin` WHERE id = ');
define('SQL_GET_ADMIN_BY_USERNAME', 'SELECT `id`, `username`, `email`, `password` FROM `admin` where `username` = ');
define('SQL_GET_ADMIN_BY_EMAIL', 'SELECT `id`, `username`, `email`, `password` FROM `admin` where `email` = ');

// get ids by item id
define("SQL_FILES_ID_BY_ITEM_ID", "SELECT `file_id` FROM `file_has_item` WHERE `item_id` = ");
define("SQL_SUBJECTS_ID_BY_ITEM_ID", "SELECT `subject_id` FROM `item_has_subject` WHERE `item_id` = ");
define("SQL_AUTHORS_ID_BY_ITEM_ID", "SELECT `author_id` FROM `author_has_item` WHERE `item_id` = ");
define("SQL_IMAGE_ID_BY_ITEM_ID", "SELECT `image_id` FROM `item` WHERE `id` = ");

// delete by id
define('SQL_DELETE_IMAGE_BY_ID', 'DELETE FROM `image` WHERE `image`.`id` = ');
define('SQL_DELETE_ITEM_BY_ID', 'DELETE FROM `item` WHERE `item`.`id` = ');
define('SQL_DELETE_SUBJECT_BY_ID', 'DELETE FROM `subject` WHERE id = ');
define('SQL_DELETE_AUTHOR_BY_ID', 'DELETE FROM `author` WHERE id = ');
define('SQL_DELETE_FILE_BY_ID', 'DELETE FROM `file` WHERE `file`.`id` = ');
define('SQL_DELETE_TYPE_BY_ID', 'DELETE FROM `type` WHERE `type`.`id` = ');
define('SQL_DELETE_ADMIN_BY_ID', 'DELETE FROM `admin` WHERE `admin`.`id` = ');

// get all by no relation
define('SQL_GET_ORPHANED_FILES', 'SELECT f.id, f.`path`, f.`content`, f.`filename`, f.`type`, f.`size` FROM `file` f WHERE NOT EXISTS (SELECT * FROM file_has_item fi WHERE f.id = fi.item_id)');
define('SQL_GET_ORPHANED_AUTHORS', 'SELECT a.id, a.author_name FROM author a WHERE NOT EXISTS (SELECT * FROM author_has_item ai WHERE a.id = ai.author_id)');
define('SQL_GET_ORPHANED_SUBJECTS', 'SELECT a.id, a.`subject` FROM `subject` a WHERE NOT EXISTS (SELECT * FROM item_has_subject ai WHERE a.id = ai.subject_id)');
define('SQL_GET_ORPHANED_IMAGES', 'SELECT a.id FROM `image` a WHERE NOT EXISTS (SELECT * FROM item i WHERE a.id = i.image_id)');
define('SQL_GET_ORPHANED_TYPES', 'SELECT a.id, a.`type` FROM `type` a WHERE NOT EXISTS (SELECT * FROM item i WHERE a.id = i.type_id)');

//insert admin
define('SQL_INSERT_ADMIN', 'INSERT INTO `admin` (`email`, `username`, `password`) VALUES ');

//update admin
define('SQL_UPDATE_ADMIN_BY_ID', 'UPDATE `admin` SET ');

