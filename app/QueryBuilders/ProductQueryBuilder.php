<?php

namespace App\QueryBuilders;

use App\Models\Product;
use App\Models\Taxon;
use App\Models\Taxonomy;

class ProductQueryBuilder
{
    /** @var \Illuminate\Database\Query\Builder */
    private $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = Product::query();
    }

    /**
     * Builds a query for products by taxonomy and taxons slugs.
     *
     * @param array $rootTaxons Array of root taxon slugs.
     * @param array $taxons Array of taxon slugs.
     * @param string|null $taxonomySlug
     * @return \Illuminate\Database\Query\Builder
     */
    public function build(array $rootTaxons = [], array $taxons = [], ?string $taxonomySlug = null)
    {
        $taxonomy = $taxonomySlug
            ? Taxonomy::findOneBySlug($taxonomySlug)
            : null;

        if (! empty($taxons)) {
            $this->handleTaxons($taxons, $rootTaxons, $taxonomy->id ?? null);
        } else if (! empty($rootTaxons)) {
            $this->handleOnlyRootTaxons($rootTaxons, $taxonomy->id ?? null);
        } else if ($taxonomy) {
            $this->handleOnlyTaxonomy($taxonomy->id);
        }

        return $this->queryBuilder;
    }

    /**
     * Handle array of taxon slugs, and add taxons to query builder
     *
     * @param array $taxons Array of taxon slugs.
     * @return void
     */
    private function handleTaxons(array $taxons, array $rootTaxonSlugs = [], ?int $taxonomyId = null): void
    {
        foreach ($taxons as $index => $taxonSlug) {
            $this->handleTaxon($taxonSlug, $rootTaxonSlugs, $taxonomyId);
        }
    }

    /**
     * Add taxon to query builder.
     *
     * @param string $taxonSlug
     * @param string|null $rootTaxonSlug
     * @return void
     */
    private function handleTaxon(string $taxonSlug, array $rootTaxonSlugs = [], ?int $taxonomyId = null): void
    {
        $this->queryBuilder->orWhereHas('taxons',
            function($query) use ($taxonSlug, $rootTaxonSlugs, $taxonomyId) {
                $query->where('slug', $taxonSlug);

                if (! empty($rootTaxonSlugs)) {
                    $query->whereHas('parent', function($q) use ($rootTaxonSlugs, $taxonomyId) {
                        $q->whereIn('slug', $rootTaxonSlugs);
                    });
                }

                if ($taxonomyId) {
                    $query->where('taxonomy_id', $taxonomyId);
                }
            });
    }

    /**
     * Create query builder with root taxons.
     *
     * @param string $rootTaxonSlug
     * @param integer|null $taxonomyId
     * @return void
     */
    private function handleOnlyRootTaxons(array $rootTaxonSlugs, ?int $taxonomyId = null) : void
    {
        $this->queryBuilder->orWhereHas('taxons',
            function($query) use ($rootTaxonSlugs, $taxonomyId) {
                $query->whereHas('parent', function($q) use ($rootTaxonSlugs, $taxonomyId) {
                    $q->whereIn('slug', $rootTaxonSlugs);

                    if ($taxonomyId) {
                        $q->where('taxonomy_id', $taxonomyId);
                    }
                });
            });
    }

    /**
     * Create query builder with only taxonomy.
     *
     * @param integer $taxonomyId
     * @return void
     */
    private function handleOnlyTaxonomy(int $taxonomyId) : void
    {
        $this->queryBuilder->orWhereHas('taxons',
            function($query) use ($taxonomyId) {
                $query->whereHas('parent', function($q) use ($taxonomyId) {
                    $q->where('taxonomy_id', $taxonomyId);
                });
            });
    }
}
