<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;

class PostController extends Controller
{
    // get all posts
    public function index()
    {
        return response([
            'posts' => Post::orderBy('created_at', 'desc')
                ->with('user:id,name,phone_number,image') // Include phone_number here
                ->withCount('comments', 'likes')
                ->with('likes', function($query) {
                    $query->where('user_id', auth()->user()->id)
                          ->select('id', 'user_id', 'post_id');
                })
                ->get()
        ], 200);
    }

    // get single post
    public function show($id)
    {
        $post = Post::where('id', $id)
            ->with('user:id,name,phone_number,image') // Include phone_number here
            ->withCount('comments', 'likes')
            ->first();

        if (!$post) {
            return response([
                'message' => 'Post not found.'
            ], 404);
        }

        return response([
            'post' => $post
        ], 200);
    }

    // create a post
    public function store(Request $request)
    {
        // validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'image' => 'nullable|image' // Optional image validation
        ]);

        $image = $this->saveImage($request->file('image'), 'posts');

        $post = Post::create([
            'body' => $attrs['body'],
            'user_id' => auth()->user()->id,
            'image' => $image
        ]);

        return response([
            'message' => 'Post created.',
            'post' => $post
        ], 200);
    }

    // update a post
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response([
                'message' => 'Post not found.'
            ], 404);
        }

        if ($post->user_id != auth()->user()->id) {
            return response([
                'message' => 'Permission denied.'
            ], 403);
        }

        // validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'image' => 'nullable|image' // Optional image validation
        ]);

        $post->update([
            'body' => $attrs['body']
        ]);

        if ($request->hasFile('image')) {
            $post->image = $this->saveImage($request->file('image'), 'posts');
            $post->save();
        }

        return response([
            'message' => 'Post updated.',
            'post' => $post
        ], 200);
    }

    // delete post
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response([
                'message' => 'Post not found.'
            ], 404);
        }

        if ($post->user_id != auth()->user()->id) {
            return response([
                'message' => 'Permission denied.'
            ], 403);
        }

        $post->comments()->delete();
        $post->likes()->delete();
        $post->delete();

        return response([
            'message' => 'Post deleted.'
        ], 200);
    }

    // Save image method
    public function saveImage($image, $folder = 'public')
    {
        if ($image) {
            $path = $image->store($folder, 'public');
            return $path;
        }
        return null;
    }
}
