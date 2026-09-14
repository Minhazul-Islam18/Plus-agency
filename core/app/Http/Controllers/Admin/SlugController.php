<?php

namespace App\Http\Controllers\Admin;

use App\Blog;
use App\Http\Controllers\Controller;
use App\Portfolio;
use App\Tender;
use Illuminate\Http\Request;

/**
 * Backs the "Regenerate URL" button on Portfolio/Blog/Tender edit forms —
 * runs the same intelligent_slug()/unique_intelligent_slug() process used
 * on create, live, so the admin SEES the actual result and can still tweak
 * it before saving (rather than a blind checkbox they only find out the
 * effect of after submit).
 */
class SlugController extends Controller
{
    public function preview(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'module' => 'required|in:portfolio,blog,tender',
            'id' => 'nullable|integer',
        ]);

        $id = $request->id;

        $existsCallback = match ($request->module) {
            'portfolio' => fn ($s) => Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($s)])->when($id, fn ($q) => $q->where('id', '!=', $id))->exists(),
            'blog' => fn ($s) => Blog::whereRaw('LOWER(slug) = ?', [strtolower($s)])->when($id, fn ($q) => $q->where('id', '!=', $id))->exists(),
            'tender' => fn ($s) => Tender::whereRaw('LOWER(slug) = ?', [strtolower($s)])->when($id, fn ($q) => $q->where('id', '!=', $id))->exists(),
        };

        return response()->json([
            'slug' => unique_intelligent_slug($request->title, $existsCallback),
        ]);
    }
}
