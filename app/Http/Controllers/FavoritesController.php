<?php

namespace App\Http\Controllers;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'post_id' => 'required|exists:posts,id',
    ]);

    $favorite = Favorite::create([
        'user_id' => $request->user()->id,
        'post_id' => $request->post_id,
    ]);

    return response()->json(['message' => 'Post added to favorites', 'favorite' => $favorite], 201);
}

public function destroy($postId)
{
    $favorite = Favorite::where('user_id', auth()->id())->where('post_id', $postId)->firstOrFail();
    $favorite->delete();

    return response()->json(['message' => 'Post removed from favorites']);
}

public function index()
{
    $favorites = Favorite::where('user_id', auth()->id())->with('post')->get();
    return response()->json($favorites);
}

}
