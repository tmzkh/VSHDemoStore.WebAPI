<?php

namespace Tests\Feature\Taxons;

use App\Models\Taxon;
use App\Models\Taxonomy;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetTaxonsTest extends TestCase
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
    public function itReturnsTaxonsListing()
    {
        $this->json('GET', '/api/taxons')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'parent_id',
                        'taxonomy_id',
                        'name',
                        'slug'
                    ]
                ],
                'meta' => [],
                'links' => []
            ])
            ->assertJsonCount(12, 'data');
    }

    /** @test */
    public function itCanAddParents()
    {
        $this->json('GET', '/api/taxons?withParent=true')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'parent_id',
                        'taxonomy_id',
                        'name',
                        'slug',
                        'parent' => [
                            'id',
                            'parent_id',
                            'taxonomy_id',
                            'name',
                            'slug',
                        ]
                    ]
                ],
                'meta' => [],
                'links' => []
            ]);
    }

    /** @test */
    public function itCanScopeOnlyRootTaxons()
    {
        $this->json('GET', '/api/taxons?onlyRoots=true')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => [
                    ['slug' => 'men'],
                    ['slug' => 'women'],
                    ['slug' => 'unisex'],
                ]
            ]);
    }

    /** @test */
    public function itCanScopeByTaxonomy()
    {
        $fakeTaxonomy = Taxonomy::create([
            'name' => 'Fake taxonomy'
        ]);

        // should not be fetched
        $fakeTaxon = Taxon::create([
            'name' => 'Fake parent taxon',
            'taxonomy_id' => $fakeTaxonomy->id
        ]);

        $this->json('GET', '/api/taxons?onlyRoots=true&taxonomy=clothes')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => [
                    ['slug' => 'men'],
                    ['slug' => 'women'],
                    ['slug' => 'unisex'],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    ['slug' => $fakeTaxon->slug],
                ]
            ]);
    }
}
