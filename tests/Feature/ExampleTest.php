<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_serves_the_home_page(): void
    {
        $this->get('/')->assertOk();
        $this->get('/index2')->assertOk();
    }
}
