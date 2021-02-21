<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaxonResourceCollection;
use App\Models\Taxon;
use Illuminate\Http\Request;

class TaxonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Taxon::query();

        if ($request->query('onlyRoots')) {
            $query = $query->roots();
        }

        if ($taxonomySlug = $request->query('taxonomy')) {
            $query->whereHas('taxonomy', function($q) use ($taxonomySlug) {
                $q->whereSlug($taxonomySlug);
            });
        }

        if ($request->query('withParent')) {
            $query->with('parent');
        }

        if ($request->query('withChildren')) {
            $query->with('children');
        }

        $query->select('id', 'parent_id', 'taxonomy_id', 'name', 'slug');

        return TaxonResourceCollection::make($query->paginate(15));
    }
}
