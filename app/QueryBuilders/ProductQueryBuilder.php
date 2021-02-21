<?php

namespace App\QueryBuilders;

use App\Models\Product;
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
     * @param array $taxons Array of taxon slugs.
     * @param string|null $taxonomySlug
     * @return void
     */
    public function build(array $taxons = [], ?string $taxonomySlug = null)
    {
        $taxonomy = $taxonomySlug
            ? Taxonomy::findOneBySlug($taxonomySlug)
            : null;

        if ($taxons) {
            $this->handleTaxons($taxons, $taxonomy->id ?? null);
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
    private function handleTaxons(array $taxons, ?int $taxonomyId = null): void
    {
        $rootTaxonSlug = $this->getRootTaxonSlug($taxons);

        if ($rootTaxonSlug
            && count($taxons) == 1
            && $taxons[0] == $rootTaxonSlug
        ){
            $this->handleOnlyRootTaxon($rootTaxonSlug, $taxonomyId);

            return;
        }

        foreach ($taxons as $index => $taxonSlug) {
            if ($taxonSlug != $rootTaxonSlug) {
                $this->handleTaxon($taxonSlug, $rootTaxonSlug, $taxonomyId);
            }
        }
    }

    /**
     * Get first slug of root taxon from array.
     *
     * NOTE: not so great way to handle this, but for now it will do.
     *
     * @param array $taxons Array of taxon slugs.
     * @return string|null
     */
    private function getRootTaxonSlug(array $taxons): ?string
    {
        if (in_array('men', $taxons)) {
            return 'men';
        }

        if (in_array('women', $taxons)) {
            return 'women';
        }

        if (in_array('other', $taxons)) {
            return 'other';
        }

        return null;
    }

    /**
     * Add taxon to query builder.
     *
     * @param string $taxonSlug
     * @param string|null $rootTaxonSlug
     * @return void
     */
    private function handleTaxon(string $taxonSlug,?string $rootTaxonSlug = null, ?int $taxonomyId = null): void
    {
        $this->queryBuilder->orWhereHas('taxons',
            function($query) use ($taxonSlug, $rootTaxonSlug, $taxonomyId) {
                $query->where('slug', $taxonSlug);

                if ($rootTaxonSlug) {
                    $query->whereHas('parent', function($q) use ($rootTaxonSlug, $taxonomyId) {
                        $q->where('slug', $rootTaxonSlug);
                    });
                }

                if ($taxonomyId) {
                    $query->where('taxonomy_id', $taxonomyId);
                }
            });
    }

    /**
     * Create query builder with root taxon.
     *
     * @param string $rootTaxonSlug
     * @param integer|null $taxonomyId
     * @return void
     */
    private function handleOnlyRootTaxon(string $rootTaxonSlug, ?int $taxonomyId = null) : void
    {
        $this->queryBuilder->orWhereHas('taxons',
            function($query) use ($rootTaxonSlug, $taxonomyId) {
                $query->whereHas('parent', function($q) use ($rootTaxonSlug, $taxonomyId) {
                    $q->where('slug', $rootTaxonSlug);

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
