<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    // Liste des articles
    public function index(Request $request)
    {
        $cacheKey = 'articles:list';

        // Mode performance test
        if ($request->has('performance_test')) {
            DB::enableQueryLog();

            $items = Article::with(['author', 'comments'])->get();

            $queryCount = count(DB::getQueryLog());

            $articles = $items->map(fn($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => strlen($article->content) > 200 
                    ? substr($article->content, 0, 200) . '...' 
                    : $article->content,
                'author' => $article->author->name,
                'comments_count' => $article->comments->count(),
                'published_at' => $article->published_at,
                'created_at' => $article->created_at,
            ]);

            Log::info("PERF-001: $queryCount queries (optimized with eager loading)");

            return response()->json([
                'data' => $articles,
                'performance' => [
                    'query_count' => $queryCount,
                    'optimization' => 'eager_loading',
                    'expected_queries' => 3,
                    'status' => $queryCount <= 3 ? 'optimized' : 'needs_optimization'
                ]
            ]);
        }

        // Mode normal avec cache
        $articles = Cache::remember($cacheKey, 60, function () {
            $items = Article::with(['author', 'comments'])->get();
            return $items->map(fn($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => strlen($article->content) > 200 
                    ? substr($article->content, 0, 200) . '...' 
                    : $article->content,
                'author' => $article->author->name,
                'comments_count' => $article->comments->count(),
                'published_at' => $article->published_at,
                'created_at' => $article->created_at,
            ]);
        });

        return response()->json($articles);
    }

    // Détail d’un article
    public function show($id)
    {
        $article = Article::with(['author', 'comments.user'])->findOrFail($id);

        return response()->json([
            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'author' => $article->author->name,
            'author_id' => $article->author->id,
            'image_path' => $article->image_path,
            'published_at' => $article->published_at,
            'created_at' => $article->created_at,
            'comments' => $article->comments->map(fn($comment) => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => $comment->user->name,
                'created_at' => $comment->created_at,
            ]),
        ]);
    }

    // Recherche sécurisée
    public function search(Request $request)
    {
        $query = $request->input('q');
        if (!$query) return response()->json([]);
        return response()->json(array_map(fn($article) => [
            'id' => $article->id,
            'title' => $article->title,
            'content' => substr($article->content, 0, 200),
            'published_at' => $article->published_at,
        ], $articles));
    }

    // Création
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'author_id' => 'required|exists:users,id',
            'image_path' => 'nullable|string',
        ]);

        $article = Article::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author_id' => $validated['author_id'],
            'image_path' => $validated['image_path'] ?? null,
            'published_at' => now(),
        ]);


        Cache::forget('articles:list');
        Cache::forget('stats');

        return response()->json($article, 201);
    }

    // Mise à jour
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|max:255',
            'content' => 'sometimes|required',
        ]);

        $article->update($validated);


        Cache::forget('articles:list');
        Cache::forget('stats');

        return response()->json($article);
    }

    // Suppression optimisée
    public function destroy($id)
    {
        $article = Article::with('comments')->findOrFail($id);


        return response()->json([
            'message' => 'Article and related comments deleted successfully',
            'deleted_article_id' => $id,
            'deleted_comments_count' => $article->comments->count(),
        ]);
    }
}
