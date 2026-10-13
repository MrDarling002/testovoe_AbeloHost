<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Model\PostSort;
use PHPUnit\Framework\TestCase;

class PostSortTest extends TestCase
{
    public function testOrderByForDate(): void
    {
        $this->assertSame('p.published_at DESC', PostSort::Date->orderBy());
    }

    public function testOrderByForViews(): void
    {
        $this->assertSame('p.views_count DESC', PostSort::Views->orderBy());
    }

    public function testFromRequestValueFallsBackToDate(): void
    {
        $this->assertSame(PostSort::Date, PostSort::fromRequestValue(null));
        $this->assertSame(PostSort::Date, PostSort::fromRequestValue(['hack']));
        $this->assertSame(PostSort::Date, PostSort::fromRequestValue('invalid'));
        $this->assertSame(PostSort::Date, PostSort::fromRequestValue(42));
    }

    public function testFromRequestValueParsesValidValues(): void
    {
        $this->assertSame(PostSort::Date, PostSort::fromRequestValue('date'));
        $this->assertSame(PostSort::Views, PostSort::fromRequestValue('views'));
    }
}