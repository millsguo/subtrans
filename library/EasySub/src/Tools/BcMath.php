<?php

namespace EasySub\Tools;

class BcMath
{
    public static function div($dividend,$divisor,$scale = 0)
    {
        if (function_exists('bcdiv')) {
            return bcdiv($dividend,$divisor,$scale);
        }
        return round((int)$dividend / (int) $divisor,$scale);
    }
}