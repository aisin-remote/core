<?php

namespace App\Support;

class Fiscal
{
    public static array $order = ['APR', 'MAY', 'JUN', 'JUL', 'AGT', 'SEPT', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', 'MAR'];

    public static function encodeMask(array $months): int
    {
        $map = array_flip(self::$order);
        $mask = 0;
        foreach ($months as $m) {
            $u = strtoupper($m);
            if (isset($map[$u])) $mask |= (1 << $map[$u]);
        }
        return $mask;
    }

    public static function monthOn(int $mask, string $month): bool
    {
        $i = array_search(strtoupper($month), self::$order, true);
        return $i !== false ? (bool)($mask & (1 << $i)) : false;
    }

    // FY start year (Apr–Mar)
    public static function fyStartYearFromOnYearOrDate(?int $onYear, ?string $dateYmd): int
    {
        if ($onYear) return $onYear;
        $y = (int)date('Y', $dateYmd ? strtotime($dateYmd) : time());
        $m = (int)date('n', $dateYmd ? strtotime($dateYmd) : time());
        return $m >= 4 ? $y : $y - 1;
    }
}
