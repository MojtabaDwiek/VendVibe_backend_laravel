<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserItem;
class UserItemsController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'post_id' => 'required|exists:posts,id',
    ]);

    $userItem = UserItem::create([
        'user_id' => $request->user()->id,
        'post_id' => $request->post_id,
    ]);

    return response()->json(['message' => 'Item added to user items', 'userItem' => $userItem], 201);
}

public function destroy($postId)
{
    $userItem = UserItem::where('user_id', auth()->id())->where('post_id', $postId)->firstOrFail();
    $userItem->delete();

    return response()->json(['message' => 'Item removed from user items']);
}

public function index()
{
    $userItems = UserItem::where('user_id', auth()->id())->with('post')->get();
    return response()->json($userItems);
}

}
