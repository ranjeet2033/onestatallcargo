<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of blog articles
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('q');

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
                $posts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 9);
                $categories = collect();
                return view('public.blog.index', compact('posts', 'categories', 'category', 'search'));
            }

            $query = BlogPost::where('is_published', true)->orderBy('published_at', 'desc');

            if ($category) {
                $query->where('category', $category);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $posts = $query->paginate(9)->withQueryString();
            $categories = BlogPost::where('is_published', true)->pluck('category')->unique();

            return view('public.blog.index', compact('posts', 'categories', 'category', 'search'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Blog index query fallback: ' . $e->getMessage());
            $posts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 9);
            $categories = collect();
            return view('public.blog.index', compact('posts', 'categories', 'category', 'search'));
        }
    }

    /**
     * Display a single blog article
     */
    public function show(string $slug)
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
                abort(404);
            }

            $post = BlogPost::where('slug', $slug)->where('is_published', true)->firstOrFail();
            $relatedPosts = BlogPost::where('is_published', true)
                ->where('id', '!=', $post->id)
                ->where('category', $post->category)
                ->take(3)
                ->get();

            if ($relatedPosts->count() < 3) {
                $morePosts = BlogPost::where('is_published', true)
                    ->where('id', '!=', $post->id)
                    ->whereNotIn('id', $relatedPosts->pluck('id'))
                    ->take(3 - $relatedPosts->count())
                    ->get();
                $relatedPosts = $relatedPosts->merge($morePosts);
            }

            return view('public.blog.show', compact('post', 'relatedPosts'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Blog show query error: ' . $e->getMessage());
            abort(404);
        }
    }
}
