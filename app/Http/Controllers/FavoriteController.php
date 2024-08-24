<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Post;

class FavoriteController extends Controller
{
    // Add a post to favorites
    public function store(Request $request, $postId)
    {
        $user = auth()->user();
        $post = Post::findOrFail($postId);

        $favorite = Favorite::updateOrCreate(
            ['user_id' => $user->id, 'post_id' => $post->id],
            []
        );

        return response()->json([
            'message' => 'Post added to favorites.'
        ], 200);
    }

    // Remove a post from favorites
    public function destroy($postId)
    {
        $user = auth()->user();
        $post = Post::findOrFail($postId);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'message' => 'Post removed from favorites.'
            ], 200);
        }

        return response()->json([
            'message' => 'Favorite not found.'
        ], 404);
    }

    
    // Get list of favorite posts
public function getFavorites()
{
    $user = auth()->user(); // Get the currently authenticated user
    $favorites = Favorite::where('user_id', $user->id)
        ->with(['post.user:id,name,phone_number,image']) // Eager load post user with specific columns
        // Remove the withCount calls if you don't want to include comments and likes count
        ->get();

    return response()->json([
        'favorites' => $favorites
    ], 200);
}

}
