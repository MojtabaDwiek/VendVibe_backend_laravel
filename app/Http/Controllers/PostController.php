<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    // Get all posts
    public function index()
    {
        return response([
            'posts' => Post::orderBy('created_at', 'desc')
                ->with('user:id,name,phone_number,image')
                ->withCount('comments', 'likes')
                ->with('likes', function($query) {
                    $query->where('user_id', auth()->user()->id)
                          ->select('id', 'user_id', 'post_id');
                })
                ->get()
        ], 200);
    }

    // Get single post
    public function show($id)
    {
        $post = Post::where('id', $id)
            ->with('user:id,name,phone_number,image')
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

    // Create a post
    public function store(Request $request)
    {
        // Validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'image' => 'nullable|image'
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

    // Update a post
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

        // Validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'image' => 'nullable|image'
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

    // Delete a post
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

    // Get all posts created by the authenticated user
    public function userPosts()
    {
        $user = auth()->user();
        $posts = Post::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('user:id,name,phone_number,image')
            ->withCount('comments', 'likes')
            ->get();

        return response([
            'posts' => $posts
        ], 200);
    }

    // Update a post belonging to the authenticated user
    public function updateUserPost(Request $request, $id)
    {
        $post = Post::where('user_id', auth()->user()->id)->findOrFail($id);

        // Validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'image' => 'nullable|image'
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

    // Delete a post belonging to the authenticated user
    public function deleteUserPost($id)
    {
        $post = Post::where('user_id', auth()->user()->id)->findOrFail($id);

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
