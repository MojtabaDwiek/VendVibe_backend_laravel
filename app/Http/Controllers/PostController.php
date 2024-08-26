<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;

class PostController extends Controller
{
    // Get all posts
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

    // Get single post
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

    // Create a post
    public function store(Request $request)
    {
        // Validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'price' => 'required|numeric|min:0',
            'images' => 'array', // Accepts an array of images
            'images.*' => 'string' // Each image URL should be a string
        ]);

        // Handle images
        $images = $request->input('images'); // Assuming images are URLs or base64 strings

        $post = Post::create([
            'body' => $attrs['body'],
            'user_id' => auth()->user()->id,
            'price' => $attrs['price'],
            'images' => $images
        ]);

        return response([
            'message' => 'Post created.',
            'post' => $post,
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
            'price' => 'nullable|numeric|min:0', // Price is optional during update
            'images' => 'nullable|array', // Accepts an array of images
            'images.*' => 'nullable|string' // Each image URL should be a string
        ]);

        // Update post attributes
        $post->update([
            'body' => $attrs['body'],
            'price' => $attrs['price'] ?? $post->price, // Preserve existing price if not provided
            'images' => $attrs['images'] ?? $post->images // Preserve existing images if not provided
        ]);

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
}
