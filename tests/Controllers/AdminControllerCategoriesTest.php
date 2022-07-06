<?php

namespace App\Tests\Controllers;

use Symfony\Component\Panther\PantherTestCase;

class AdminControllerCategoriesTest extends PantherTestCase
{
    public function testSomething(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
