<?php

class MathUtils
{
    public static function abs($value)
    {
        return abs($value);
    }

    public static function acos($value)
    {
        return acos($value);
    }

    public static function cos($value)
    {
        return cos($value);
    }

    public static function cross($vector1, $vector2)
    {
        // Assuming $vector1 and $vector2 are arrays of length 3
        $result = [
            $vector1[1] * $vector2[2] - $vector1[2] * $vector2[1],
            $vector1[2] * $vector2[0] - $vector1[0] * $vector2[2],
            $vector1[0] * $vector2[1] - $vector1[1] * $vector2[0]
        ];
        return $result;
    }

    public static function divide($value1, $value2)
    {
        return $value1 / $value2;
    }

    public static function dot($vector1, $vector2)
    {
        // Assuming $vector1 and $vector2 are arrays of the same length
        $result = 0;
        $length = count($vector1);
        for ($i = 0; $i < $length; $i++) {
            $result += $vector1[$i] * $vector2[$i];
        }
        return $result;
    }

    public static function max($value1, $value2)
    {
        return max($value1, $value2);
    }

    public static function min($value1, $value2)
    {
        return min($value1, $value2);
    }

    public static function multiply($value1, $value2)
    {
        return $value1 * $value2;
    }

    public static function norm($vector)
    {
        // Assuming $vector is an array
        $result = 0;
        foreach ($vector as $value) {
            $result += $value * $value;
        }
        return sqrt($result);
    }

    public static function pi()
    {
        return pi();
    }

    public static function re($complexNumber)
    {
        // Assuming $complexNumber is a real number, so it doesn't change
        return $complexNumber;
    }

    public static function sin($value)
    {
        return sin($value);
    }

    public static function subtract($value1, $value2)
    {
        // Assuming $value1 and $value2 are arrays of the same length
        $result = [];
        $length = count($value1);
        for ($i = 0; $i < $length; $i++) {
            $result[] = $value1[$i] - $value2[$i];
        }
        return $result;
    }
}
