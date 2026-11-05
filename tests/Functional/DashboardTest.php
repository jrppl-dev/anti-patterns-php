<?php

namespace App\Tests\Functional;

use App\Tests\AppWebTestCase;

class DashboardTest extends AppWebTestCase
{
    public function testAnonymousVisitorChoosesAUser(): void
    {
        $this->createUser('Alice');

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Who are you?');
    }

    public function testDashboardShowsOwnLists(): void
    {
        $alice = $this->createUser('Alice');
        $this->createList($alice, 'Groceries');
        $this->loginAs($alice);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('table', 'Groceries');
    }
}
