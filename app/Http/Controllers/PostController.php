<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\UserItem; // Import the UserItem model
use App\Models\Favorite; // Import the Favorite model if you need it
use Illuminate\Support\Facades\Storage;

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

    public function store(Request $request)
    {
        // Validate fields
        $attrs = $request->validate([
            'body' => 'required|string',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array', // Accepts an array of files
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048' // File validation rules
        ]);
    
        // Handle images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public'); // Save the file to the 'posts' directory
                $images[] = $path;
            }
        }
    
        $post = Post::create([
            'body' => $attrs['body'],
            'user_id' => auth()->user()->id,
            'price' => $attrs['price'],
            'images' => $images
        ]);
    
        // Automatically add the post to the user's items
        UserItem::create([
            'user_id' => auth()->user()->id,
            'post_id' => $post->id,
        ]);

        return response([
            'message' => 'Post created and added to user items.',
            'post' => $post,
        ], 201);
    }

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
            'images' => 'nullable|array', // Accepts an array of files
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048' // File validation rules
        ]);

        // Handle images
        $images = $post->images; // Preserve existing images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public'); // Save the file to the 'posts' directory
                $images[] = $path;
            }
        }

        // Update post attributes
        $post->update([
            'body' => $attrs['body'],
            'price' => $attrs['price'] ?? $post->price, // Preserve existing price if not provided
            'images' => $images // Update images
        ]);

        return response([
            'message' => 'Post updated.',
            'post' => $post
        ], 200);
    }

    public function search(Request $request)
    {
        $query = $request->input('query', '');

        // Fetch posts with user information
        $posts = Post::where('body', 'LIKE', "%{$query}%")
                     ->with('user:id,phone_number,image') // Include user info (phone_number, image)
                     ->get()
                     ->map(function ($post) {
                         // Format the response
                         return [
                             'id' => $post->id,
                             'body' => $post->body,
                             'price' => $post->price,
                             'images' => $post->images, // Ensure images are accessible
                             'user' => [
                                 'phone_number' => $post->user->phone_number,
                                 'image' => $post->user->image,
                             ],
                         ];
                     });

        return response()->json(['posts' => $posts]);
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
