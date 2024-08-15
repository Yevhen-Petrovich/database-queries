<?php

namespace DevTest;

use Exception;
use mysqli;

class PlaceholderReplacementServices
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function replacePlaceholders(string $query, array $args): string
    {
        $index = 0;
        $pattern = '/\?([dfa#]?)/';

        return preg_replace_callback($pattern, function ($matches) use (&$args, &$index) {
            $type = $matches[1];
            $value = $args[$index++];

            switch ($type) {
                case 'd':
                    return (int)$value;
                case 'f':
                    return (float)$value;
                case 'a':
                    return $this->handleArray($value);
                case '#':
                    return $this->handleIdentifier($value);
                default:
                    return $this->handleDefault($value);
            }
        }, $query);
    }

    private function handleArray($value): string
    {
        if (!is_array($value)) {
            throw new Exception('Expected array for placeholder ?a');
        }

        if ($this->isAssociativeArray($value)) {
            $result = [];
            foreach ($value as $key => $val) {
                $result[] = "`$key` = " . $this->handleDefault($val);
            }
            return implode(', ', $result);
        } else {
            return implode(', ', array_map([$this, 'handleDefault'], $value));
        }
    }

    private function handleIdentifier($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, 'quoteIdentifier'], $value));
        }
        return $this->quoteIdentifier($value);
    }

    private function handleDefault($value): string
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif (is_string($value)) {
            return "'" . $this->mysqli->real_escape_string($value) . "'";
        } else {
            return $value;
        }
    }

    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function quoteIdentifier($identifier): string
    {
        return "`" . $this->mysqli->real_escape_string($identifier) . "`";
    }
}