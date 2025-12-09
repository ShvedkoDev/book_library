<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        // Get all published CMS pages
        $pages = Page::published()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'updated_at']);

        return view('sitemap.index', compact('pages'));
    }
}
