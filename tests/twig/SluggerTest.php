<?php

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Twig\AppExtension;

class SluggerTest extends TestCase
{
    public function testSlugify(string $string, string $slug): void
    {
        $slugger = new AppExtension();
        $this->assertSame($slug, $slugger->slugify('Cell Phones'));
    }

    public function getSlug(): void
    {

    }
}
