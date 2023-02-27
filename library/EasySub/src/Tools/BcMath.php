<?php

namespace EasySub\Tools;

class BcMath
{
    /**
     * 检查扩展是否安装
     * @return bool
     */
    protected static function checkBcMath(): bool
    {
        if (extension_loaded('bcmath')) {
            return true;
        }
        return false;
    }

    /**
     * 除法
     * @param string|int|float $dividend
     * @param string|int|float $divisor
     * @param int $scale
     * @return float|string
     */
    public static function div(string|int|float $dividend,string|int|float $divisor,int $scale = 0): float|string
    {
        if (self::checkBcMath()) {
            return bcdiv($dividend,$divisor,$scale);
        }
        return round((float)$dividend / (float) $divisor,$scale);
    }

    /**
     * 乘法
     * @param string|int|float $leftOperand
     * @param string|int|float $rightOperand
     * @param int $scale
     * @return float|string
     */
    public static function mul(string|int|float $leftOperand,string|int|float $rightOperand,int $scale = 0): float|string
    {
        if (self::checkBcMath()) {
            return bcmul($leftOperand,$rightOperand,$scale);
        }
        return round((float)$leftOperand * (float)$rightOperand,$scale);
    }

    /**
     * 加法
     * @param string|float|int $leftOperand
     * @param string|float|int $rightOperand
     * @param int $scale
     * @return float|string
     */
    public static function add(string|float|int $leftOperand,string|float|int $rightOperand,int $scale = 0): float|string
    {
        if (self::checkBcMath()) {
            return bcadd($leftOperand,$rightOperand,$scale);
        }
        return round((float)$leftOperand + (float)$rightOperand,$scale);
    }

    /**
     * 减法
     * @param string|float|int $leftOperand
     * @param string|float|int $rightOperand
     * @param int $scale
     * @return float|string
     */
    public static function sub(string|float|int $leftOperand,string|float|int $rightOperand,int $scale = 0): float|string
    {
        if (self::checkBcMath()) {
            return bcsub($leftOperand,$rightOperand,$scale);
        }
        return round((float)$leftOperand - (float)$rightOperand,$scale);
    }
}