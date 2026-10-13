<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function testBuildReturnsEmptyForSinglePage(): void
    {
        $this->assertSame([], Pagination::build(1, 1, '/category/1', 'date'));
    }

    public function testBuildMarksCurrentPageActive(): void
    {
        $items  = Pagination::build(2, 3, '/category/1', 'date');
        $active = array_filter($items, static fn(array $i): bool => $i['active']);

        $this->assertCount(1, $active);
        $this->assertSame('2', array_values($active)[0]['label']);
    }

    public function testBuildFirstPageWindow(): void
    {
        $items  = Pagination::build(1, 20, '/category/1', 'date');
        $labels = array_column($items, 'label');

        $this->assertContains('1', $labels);
        $this->assertContains('5', $labels);
        $this->assertContains('20', $labels);
        $this->assertContains('…', $labels);
    }

    public function testBuildLastPageWindow(): void
    {
        $items  = Pagination::build(20, 20, '/category/1', 'date');
        $labels = array_column($items, 'label');

        $this->assertContains('1', $labels);
        $this->assertContains('16', $labels);
        $this->assertContains('20', $labels);
    }

    public function testBuildMiddleWindow(): void
    {
        $items  = Pagination::build(10, 20, '/category/1', 'date');
        $labels = array_column($items, 'label');

        $this->assertContains('1', $labels);
        $this->assertContains('8', $labels);
        $this->assertContains('10', $labels);
        $this->assertContains('12', $labels);
        $this->assertContains('20', $labels);
    }

    public function testBuildSmallTotalShowsAllPages(): void
    {
        $items  = Pagination::build(2, 5, '/category/1', 'date');
        $labels = array_filter(
            array_column($items, 'label'),
            static fn(string $l): bool => is_numeric($l)
        );

        $this->assertContains('1', $labels);
        $this->assertContains('5', $labels);
    }
}