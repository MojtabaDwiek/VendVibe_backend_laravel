<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public Routes
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Post
    Route::get('/posts', [PostController::class, 'index']); // Get all posts
    Route::post('/posts', [PostController::class, 'store']); // Create a post
    Route::get('/posts/{id}', [PostController::class, 'show']); // Get a single post
    Route::put('/posts/{id}', [PostController::class, 'update']); // Update a post
    Route::delete('/posts/{id}', [PostController::class, 'destroy']); // Delete a post

    // User's Posts
    Route::get('/myitems', [PostController::class, 'userPosts']); // Get authenticated user's posts
    Route::put('/myitems/{id}', [PostController::class, 'updateUserPost']); // Update authenticated user's post
    Route::delete('/myitems/{id}', [PostController::class, 'deleteUserPost']); // Delete authenticated user's post

    // Comment
    Route::get('/posts/{id}/comments', [CommentController::class, 'index']); // Get all comments of a post
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']); // Create a comment on a post
    Route::put('/comments/{id}', [CommentController::class, 'update']); // Update a comment
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']); // Delete a comment

    // Like
    Route::post('/posts/{id}/likes', [LikeController::class, 'likeOrUnlike']); // Like or unlike a post

    // Favorite
    Route::post('/posts/{postId}/favorite', [FavoriteController::class, 'store']); // Add a post to favorites
    Route::delete('/posts/{postId}/favorite', [FavoriteController::class, 'destroy']); // Remove a post from favorites
    Route::get('/user/favorites', [FavoriteController::class, 'getFavorites']); // Get list of favorite posts
});
