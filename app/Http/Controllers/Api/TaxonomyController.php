<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxonomyResourceCollection;
use App\Models\Taxonomy;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string $categorySlug
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TaxonomyResourceCollection::make(
            Taxonomy::select('id', 'name', 'slug')->get());
    }
}
