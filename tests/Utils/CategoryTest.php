<?php

namespace App\Tests\Utils;

use App\Utils\CategoryTreeFrontPage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestcase;

class CategoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $urlGenerator = $kernel->getContainer()->get('router');


    }
}
