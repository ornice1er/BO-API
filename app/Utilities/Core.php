<?php

namespace App\Utilities;

use sirajcse\UniqueIdGenerator\UniqueIdGenerator;

class Core
{
    public static function generateUniqueCode($tableClass, $codeLength, $prefix = null)
    {

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);
        $prefixe = $prefix.'-';
        $code = '';

        while (strlen($code) < $codeLength) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code.$character;
        }

        if ($tableClass::where('code', $prefixe.$code)->exists()) {
            return self::generateUniqueCode($prefix, $tableClass, $codeLength);
        }

        return $prefixe.$code;

    }

    public static function generateIncrementUniqueCode($table, $length, $field, $prefix)
    {
        return UniqueIdGenerator::generate(['table' => $table, 'length' => $length, 'field' => $field, 'prefix' => $prefix]);
    }
}
