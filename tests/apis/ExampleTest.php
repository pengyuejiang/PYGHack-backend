<?php
namespace Tests\Units\Apis;

use Laravel\Lumen\Testing\DatabaseMigrations;

class AdminApiTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        echo "🚩 API Test: admin\n\n";
        parent::setUp();
    }
}
