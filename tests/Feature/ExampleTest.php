<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_products_endpoint_returns_paginated_response(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonStructure([
            'current_page',
            'data',
            'per_page',
            'total',
        ]);
    }
}
