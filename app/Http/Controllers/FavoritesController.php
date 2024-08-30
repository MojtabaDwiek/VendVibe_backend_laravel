<?php
namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritesController extends Controller
{
    // Add a post to favorites
    public function store(Request $request)
    {
        // Validate the post_id exists in the posts table
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        // Get the authenticated user
        $userId = $request->user()->id;
        $postId = $request->post_id;

        // Check if the post is already favorited
        if (Favorite::where('user_id', $userId)->where('post_id', $postId)->exists()) {
            return response()->json(['message' => 'Post is already in favorites'], 400);
        }

        // Create a new favorite
        $favorite = Favorite::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);

        // Fetch the post details
        $post = Post::find($postId);

        return response()->json([
            'message' => 'Post added to favorites',
            'favorite' => $favorite,
            'post' => $post // Include post details
        ], 201);
    }

    // Remove a post from favorites
    public function destroy($postId)
    {
        // Find the favorite entry for the authenticated user and the specified post
        $favorite = Favorite::where('user_id', Auth::id())->where('post_id', $postId)->first();

        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found'], 404);
        }

        // Delete the favorite
        $favorite->delete();

        return response()->json(['message' => 'Post removed from favorites']);
    }

    // Retrieve all favorite posts for the authenticated user
    public function index()
    {
        // Fetch favorites with related post data
        $favorites = Favorite::where('user_id', Auth::id())->with('post')->get();

        return response()->json($favorites);
    }
}
