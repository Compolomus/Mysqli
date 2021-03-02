<?php

declare(strict_types=1);

namespace Compolomus\Mysqli;

use mysqli;
use mysqli_driver;
use mysqli_stmt;
use stdClass;

class Wrapper
{
    public const FETCHTOARRAY = 0;

    public const FETCHTOOBJECT = 1;

    private mysqli $mysqli;

    private ?mysqli_stmt $stmt = null;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_STRICT;
    }

    public function query(string $query, ?array $placeholders = null)
    {
        $this->stmt = $this->mysqli->prepare($query);

        if ($placeholders && $this->countParams()) {
            $this->bindParam($placeholders);
        }

        $this->stmt->execute();

        if (!$this->isFetch($query)) {
            return $this->stmt->affected_rows;
        }

        if ($placeholders) {
            $this->bindResult();
        }

        return $this;
    }

    public function result($mode = self::FETCHTOARRAY)
    {
        $data = [];
        $method = $mode ? 'fetch_object' : 'fetch_assoc';

        $res = $this->stmt->get_result();
        while ($row = $res->$method()) {
            $data[] = $row;
        }

        return $data;
    }


    private function bindResult(): void
    {
        call_user_func_array([$this->stmt, 'bind_result'], $this->getMeta());
    }

    private function getMeta(): array
    {
        $binds = [];
        $meta = $this->stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $binds[] = &$row[$field->name];
        }

        return $binds;
    }

    private function bindParam(array $placeholders): void
    {
        @call_user_func([$this->stmt, 'bind_param'], $this->placeholdersTypes($placeholders), ...$placeholders);
    }

    private function placeholdersTypes(array $placeholders): string
    {
        return implode('', array_map([$this, 'types'], $placeholders));
    }

    private function types($value): string
    {
        return is_numeric($value)
            ? (is_float($value) ? 'd' : 'i') // int or float
            : (strlen((string)$value) <= 65536 ? 's' : 'b'); // string or blob
    }

    private function countParams(): int
    {
        return $this->stmt->param_count;
    }

//    private function countFields(): int
//    {
//        return $this->stmt->field_count;
//    }

    private function isFetch(string $query): bool
    {
        return false !== stripos($query, 'select');
    }

//    private function reset(): void
//    {
//        // set def values post execute
////        $this->query = null;
//        $this->placeholders = null;
//    }
}
