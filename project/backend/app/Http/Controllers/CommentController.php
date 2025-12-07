<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Get comments for an article.
     */
    public function index($articleId)
    {
        $comments = Comment::where('article_id', $articleId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $comment = Comment::create($validated);
        $comment->load('user');

        return response()->json($comment, 201);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $articleId = $comment->article_id;
        $comment->delete();
        
        // ✅ Correction: vérifier si des commentaires restent
        $remainingComments = Comment::where('article_id', $articleId)->get();
        
        return response()->json([
            'success' => true,
            'remaining_count' => $remainingComments->count(),
            'first' => $remainingComments->first() // Renvoie null si vide
        ]);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);

        return response()->json($comment);
    }
}

