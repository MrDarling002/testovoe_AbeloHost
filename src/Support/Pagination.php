<?php

declare(strict_types=1);

namespace App\Support;

final class Pagination
{
    /** @return list<array{url: ?string, label: string, active: bool}> */
    public static function build(int $current, int $total, string $baseUrl, string $sort): array
    {
        if ($total <= 1) {
            return [];
        }

        $items = [];

        $winStart = 1;
        $winEnd   = $total;

        if ($total > 7) {
            $winStart = max(1, min($current - 2, $total - 4));
            $winEnd   = min($total, $winStart + 4);
        }

        if ($current > 1) {
            $items[] = ['url' => self::url($baseUrl, $sort, 1), 'label' => '«', 'active' => false];
            $items[] = ['url' => self::url($baseUrl, $sort, $current - 1), 'label' => '‹', 'active' => false];
        }

        if ($winStart > 1) {
            $items[] = ['url' => self::url($baseUrl, $sort, 1), 'label' => '1', 'active' => false];
            if ($winStart > 2) {
                $items[] = ['url' => null, 'label' => '…', 'active' => false];
            }
        }

        for ($i = $winStart; $i <= $winEnd; $i++) {
            $items[] = [
                'url'    => $i === $current ? null : self::url($baseUrl, $sort, $i),
                'label'  => (string) $i,
                'active' => $i === $current,
            ];
        }

        if ($winEnd < $total) {
            if ($winEnd < $total - 1) {
                $items[] = ['url' => null, 'label' => '…', 'active' => false];
            }
            $items[] = ['url' => self::url($baseUrl, $sort, $total), 'label' => (string) $total, 'active' => false];
        }

        if ($current < $total) {
            $items[] = ['url' => self::url($baseUrl, $sort, $current + 1), 'label' => '›', 'active' => false];
            $items[] = ['url' => self::url($baseUrl, $sort, $total), 'label' => '»', 'active' => false];
        }

        return $items;
    }

    private static function url(string $baseUrl, string $sort, int $page): string
    {
        return "{$baseUrl}?sort={$sort}&page={$page}";
    }
}