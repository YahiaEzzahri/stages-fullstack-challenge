<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
public function index(Request $request)
{
    $cacheKey = 'articles:list';
    
    // Mode performance test
    if ($request->has('performance_test')) {
        DB::enableQueryLog();
        
        // EAGER LOADING OPTIMISE
        $items = Article::with(['author', 'comments'])->get();
        
        // Compter les requêtes
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // Formater la réponse avec métriques
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
        
        // Ajouter les métriques de performance
        $response = [
            'data' => $articles,
            'performance' => [
                'query_count' => $queryCount,
                'optimization' => 'eager_loading',
                'expected_queries' => 3,
                'status' => $queryCount <= 3 ? 'optimized' : 'needs_optimization'
            ]
        ];
        
        Log::info("PERF-001: $queryCount queries (optimized with eager loading)");
        
        return response()->json($response);
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

    /**
     * Display the specified article.
     */
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
            'comments' => $article->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user->name,
                    'created_at' => $comment->created_at,
                ];
            }),
        ]);
    }

    /**
     * Search articles.
     */
public function search(Request $request)
{
    $query = $request->input('q');

    if (!$query) {
        return response()->json([]);
    }

    $articles = Article::where('title', 'LIKE', "%{$query}%")
        ->orWhere('content', 'LIKE', "%{$query}%")
        ->with('author')
        ->get();

    return response()->json($articles);
}


    /**
     * Store a newly created article.
     */
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

    // Invalider les caches
    Cache::forget('articles:list');
    Cache::forget('stats'); // ← Ajouter cette ligne

    return response()->json($article, 201);
}

    /**
     * Update the specified article.
     */
    public function update(Request $request, $id)
{
    $article = Article::findOrFail($id);

    $validated = $request->validate([
        'title' => 'sometimes|required|max:255',
        'content' => 'sometimes|required',
    ]);

    $article->update($validated);

    // Invalider les caches
    Cache::forget('articles:list');
    Cache::forget('stats'); // ← Ajouter cette ligne

    return response()->json($article);
}

    /**
     * Remove the specified article.
     */
    public function destroy($id)
{
    // ✅ Corriger pour supprimer un article
    $article = Article::findOrFail($id);
    $article->delete();

    // Invalider les caches
    Cache::forget('articles:list');
    Cache::forget('stats'); // Ajouter l'invalidation du cache stats

    return response()->json(['success' => true]);
}

}

