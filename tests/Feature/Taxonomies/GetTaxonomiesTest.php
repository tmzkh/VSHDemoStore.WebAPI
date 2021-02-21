<?php

namespace Tests\Feature\Taxonomies;

use App\Models\Taxonomy;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetTaxonomiesTest extends TestCase
{
    use RefreshDatabase;

    /**
    * Setup the test environment.
    *
    * @return void
    */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestCategorySeeder::class);
    }

    /** @test */
    public function itReturnsTaxonomies()
    {
        $this->json('GET', '/api/taxonomies')
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['name' => 'Clothes'],
                ]
            ]);
    }
}
