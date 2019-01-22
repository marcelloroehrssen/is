<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 12/05/2018
 * Time: 02:15.
 */

namespace App\Utils;

class CacophonySavyUtil
{
    public static function encode($text)
    {
        if (empty($text)) {
            return null;
        }
        $exploded = str_split($text);
        shuffle($exploded);

        $string = '';
        foreach ($exploded as $char) {
            $string .= self::rot($char, rand(1, 22));
        }

        return $string;
    }

    private static function rot($s, $n = 13)
    {
        static $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $n = (int) $n % 26;
        if (!$n) {
            return $s;
        }
        if (13 == $n) {
            return str_rot13($s);
        }
        for ($i = 0, $l = strlen($s); $i < $l; ++$i) {
            $c = $s[$i];
            if ($c >= 'a' && $c <= 'z') {
                $s[$i] = $letters[(ord($c) - 71 + $n) % 26];
            } elseif ($c >= 'A' && $c <= 'Z') {
                $s[$i] = $letters[(ord($c) - 39 + $n) % 26 + 26];
            }
        }

        return $s;
    }
}
