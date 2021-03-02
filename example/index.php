<?php

//declare(strict_types=1);

use Compolomus\Mysqli\Wrapper;

require_once __DIR__ . '/../vendor/autoload.php';

$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$name = 'test';

$mysqli = new mysqli($host, $user, $pass, $name, $port = 3306);
$mysqli->set_charset('utf-8');

$db = new Wrapper($mysqli);

$sql = '
    CREATE TABLE `test` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `skill` float NOT NULL,
    `text` text COLLATE utf8_unicode_ci NOT NULL,
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
';


$result = $db->query($sql);


$sql = 'INSERT INTO `test` (`name`, `skill`, `text`) VALUES (?, ?, ?);';
$placeholders = ['test', 0.8, 'test text'];

$result = $db->query($sql, $placeholders);

$sql = 'SELECT * FROM `test` WHERE `name` = ?';
$placeholders = ['test'];

$result = $db->query($sql, $placeholders)->result(Wrapper::FETCHTOOBJECT); // Wrapper::FETCHTOOBJECT

echo '<pre>' . print_r($result, true) . '</pre>';

$sql = 'SELECT COUNT(*) FROM `test`';

$result = $db->query($sql)->result();

echo '<pre>' . print_r($result, true) . '</pre>';

$sql = 'delete from `test` where `id` = ?';
$placeholders = [2];

$result = $db->query($sql, $placeholders);

echo '<pre>' . print_r($result, true) . '</pre>';

$sql = 'UPDATE `test` SET `name` = ?  WHERE (`id` = ?)';
$placeholders = ['test2', 3];

$result = $db->query($sql, $placeholders);

echo '<pre>' . print_r($result, true) . '</pre>';

$sql = 'DROP TABLE `test`;';

//$result = $db->query($sql);

echo '<pre>' . print_r($result, true) . '</pre>';
