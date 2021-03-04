<?php

declare(strict_types=1);

namespace Compolomus\Mysqli;

use mysqli;
use PHPUnit\Framework\TestCase;
use Exception;

class MysqliTest extends TestCase
{
    protected static Wrapper $object;

    protected function setUp(): void
    {
        $config = include 'config.php';

        $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['name'], $port = 3306);
        $mysqli->set_charset('utf-8');

        self::$object = new Wrapper($mysqli);
    }

    public function test__construct(): void
    {
        try {
            self::assertIsObject(self::$object);
            self::assertInstanceOf(Wrapper::class, self::$object);
        } catch (Exception $e) {
            self::assertStringContainsString('Must be initialized ', $e->getMessage());
        }
    }

    public function testQueryWithOutResult(): void
    {
        $sql = '
            CREATE TABLE `test` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `skill` float NOT NULL,
            `text` text COLLATE utf8_unicode_ci NOT NULL,
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';

        self::$object->query($sql);
        $sql = "INSERT INTO `test` (`name`, `skill`, `text`) 
                    VALUES ('test', 0.88, 'test text'), 
                           ('test1', 0.28, 'test text'), 
                           ('test2', 0.38, 'test text');";
        $result = self::$object->query($sql);
        self::assertEquals(3, $result);
    }

    public function testQueryWithOutResultWithPlaceholder(): void
    {
        $sql = 'DELETE FROM `test` WHERE `id` = ?;';
        $placeholders = [2];
        $result = self::$object->query($sql, $placeholders);
        self::assertEquals(1, $result);
    }

    public function testQueryWithResultWithOutPlaceholder(): void
    {
        $sql = 'SELECT COUNT(*) AS `count` FROM `test`;';
        $result = self::$object->query($sql)->result();
        self::assertArrayHasKey('count', $result);
        self::assertEquals(2, $result['count']);
    }

    public function testQueryWithResultWithPlaceholder(): void
    {
        $sql = 'SELECT * FROM `test` WHERE `id` in(?, ?);';
        $placeholders = [1, 3];
        $result = self::$object->query($sql, $placeholders)->result();
        self::assertCount(2, $result);
        self::assertArrayHasKey('name', $result[0]);
        self::assertArrayHasKey('text', $result[1]);
        $result = self::$object->query($sql, $placeholders)->result(Wrapper::FETCHTOOBJECT);
        self::assertObjectHasAttribute('name', $result[0]);
        self::assertObjectHasAttribute('text', $result[1]);

    }

    public static function tearDownAfterClass(): void
    {
        $sql = 'DROP TABLE `test`;';
        self::$object->query($sql);
        // DROP DATABASE `test`
    }
}
