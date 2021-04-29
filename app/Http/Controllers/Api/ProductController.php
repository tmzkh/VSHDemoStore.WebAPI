<?php

namespace App\Http\Controllers\Api;

use App\Actions\ClearProductAssetsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SaveProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductResourceCollection;
use App\Models\Product;
use App\Models\Taxon;
use App\QueryBuilders\ProductQueryBuilder;
use Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** @var \App\QueryBuilders\ProductQueryBuilder */
    private $productQueryBuilder;

    public function __construct()
    {
        $this->productQueryBuilder = new ProductQueryBuilder;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string $categorySlug
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, string $categorySlug)
    {
        $query = $this->productQueryBuilder->build(
            $request->query('rootTaxons') && is_array($request->query('rootTaxons'))
                ? $request->query('rootTaxons')
                : [],
            $request->query('taxons') && is_array($request->query('taxons'))
                ? $request->query('taxons')
                : [],
            $categorySlug);

        $query->select('id', 'name', 'slug', 'sku');

        if ($request->query('onlyFittable')) {
            $query->fittable();
        }

        return ProductResourceCollection::make(
            $query
                ->with([
                    'taxons:id,parent_id,name,slug',
                    'taxons.parent:id,parent_id,name,slug',
                    'assets' => function ($q) use ($request) {
                        if (! Auth::guard('auth0')->check() || ! $request->query('withImagesAndModels')) {
                            return $q->images();
                        }

                        return $q->imagesAndModels()
                            ->with(['materials']);
                    },
                ])
                ->withCount('models')
                ->paginate(15)
                ->appends($request->query()));
    }

    /**
     * Display a specific product.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return void
     */
    public function show(Request $request, Product $product)
    {
        $product->load([
            'taxons:id,parent_id,name,slug',
            'taxons.parent:id,parent_id,name,slug',
            'assets' => function ($q) use ($request) {
                if (! Auth::guard('auth0')->check() || ! $request->query('withImagesAndModels')) {
                    return $q->images();
                }

                return $q->imagesAndModels()
                    ->with(['materials']);
            },
        ])
            ->loadCount('models');

        return response()->json(ProductResource::make($product), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\SaveProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SaveProductRequest $request)
    {
        $requestData = $request->validated();

        $product = Product::create($requestData);

        $product->addTaxon(Taxon::find($requestData['taxon_id']));

        $product->load([
            'taxons:id,parent_id,name,slug',
            'taxons.parent:id,parent_id,name,slug'
        ]);

        return response()->json(ProductResource::make($product), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Api\SaveProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(SaveProductRequest $request, Product $product)
    {
        $requestData = $request->validated();

        $product->slug = null;

        $product->update($requestData);

        $product->load('taxons');

        $taxonId = $requestData['taxon_id'];

        $product->taxons->each(function (Taxon $taxon) use ($product, $taxonId) {
            if ($taxon->id != $taxonId) {
                $product->removeTaxon($taxon);
            }
        });

        if (! $product->taxons->contains('id', $taxonId)) {
            $product->addTaxon(Taxon::find($taxonId));
        }

        $product->load([
            'taxons:id,parent_id,name,slug',
            'taxons.parent:id,parent_id,name,slug',
            'assets' => function ($q) {
                return $q->imagesAndModels()
                    ->with(['materials']);
            },
        ])
            ->loadCount('models');

        return response()->json(ProductResource::make($product), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, ClearProductAssetsAction $clearProductAssetsAction)
    {
        if (! $clearProductAssetsAction->execute($product)) {
            return response()->json(['error' => 'Something went wrong on deleting product assets.'], 500);
        }

        $product->delete();

        return response()->json(ProductResource::make($product), 204);
    }
}
