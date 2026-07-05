<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where('status', 'published')
            ->with('author:id,name,avatar_path')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return BlogResource::collection($blogs);
    }

    public function store(Request $request)
    {
        if (! $request->user()->isSuperAdmin() && ! $request->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'sometimes|in:draft,published',
            'featured_image' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        $validated['author_id'] = $request->user()->id;
        $validated['team_id'] = $request->user()->team_id;

        if (($validated['status'] ?? 'draft') === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $blog = Blog::create($validated);

        return new BlogResource($blog->load('author:id,name,avatar_path'));
    }

    public function show(string $slugOrId)
    {
        $query = Blog::with('author:id,name,avatar_path');
        $blog = is_numeric($slugOrId)
            ? $query->findOrFail($slugOrId)
            : $query->where('slug', $slugOrId)->firstOrFail();

        return new BlogResource($blog);
    }

    public function update(Request $request, Blog $blog)
    {
        if ($request->user()->id !== $blog->author_id && ! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'status' => 'sometimes|in:draft,published',
            'featured_image' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'published' && empty($blog->published_at)) {
            $validated['published_at'] = now();
        }

        $blog->update($validated);

        return new BlogResource($blog->fresh()->load('author:id,name,avatar_path'));
    }

    public function destroy(Request $request, Blog $blog)
    {
        if ($request->user()->id !== $blog->author_id && ! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $blog->delete();

        return response()->json(['message' => 'Blog berhasil dihapus.']);
    }

    public function manage(Request $request)
    {
        $user = $request->user();

        $blogs = Blog::where('team_id', $user->team_id)
            ->with('author:id,name,avatar_path')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return BlogResource::collection($blogs);
    }
}
