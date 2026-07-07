<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where(function ($query) {
                $query->where('status', 'published')
                      ->orWhere(function ($q) {
                          $q->where('status', 'scheduled')
                            ->where('published_at', '<=', now());
                      });
            })
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

        $rules = [
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'sometimes|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'focus_keyword' => 'nullable|string|max:100',
            'seo_title' => 'nullable|string|max:70',
            'seo_description' => 'nullable|string|max:200',
            'seo_keywords' => 'nullable',
            'canonical_url' => 'nullable|url|max:255',
            'twitter_card' => 'nullable|in:summary,summary_large_image',
        ];

        if ($request->hasFile('featured_image')) {
            $rules['featured_image'] = 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240';
        } else {
            $rules['featured_image'] = 'nullable|string';
        }

        if ($request->hasFile('og_image')) {
            $rules['og_image'] = 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240';
        } else {
            $rules['og_image'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $validated['author_id'] = $request->user()->id;
        $validated['team_id'] = $request->user()->team_id;
        $validated['twitter_card'] = $validated['twitter_card'] ?? 'summary_large_image';

        if (is_string($validated['seo_keywords'] ?? null)) {
            $decoded = json_decode($validated['seo_keywords'], true);
            $validated['seo_keywords'] = is_array($decoded) ? array_values(array_filter(array_map('trim', $decoded))) : [];
        } elseif (!is_array($validated['seo_keywords'] ?? null)) {
            $validated['seo_keywords'] = [];
        }

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $teamStr = $request->user()->team ? $request->user()->team->slug : 'superadmin';
            $path = $file->storeAs(
                $teamStr . '/blog/' . date('Y/m'),
                time() . '_' . $file->getClientOriginalName(),
                'r2'
            );
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('r2');
            $validated['featured_image'] = $disk->url($path);
        }

        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $teamStr = $request->user()->team ? $request->user()->team->slug : 'superadmin';
            $path = $file->storeAs(
                $teamStr . '/blog/og/' . date('Y/m'),
                time() . '_' . $file->getClientOriginalName(),
                'r2'
            );
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('r2');
            $validated['og_image'] = $disk->url($path);
        }

        unset($validated['featured_image_raw']);

        if (($validated['status'] ?? 'draft') === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        } else if (($validated['status'] ?? 'draft') === 'scheduled' && empty($validated['published_at'])) {
            // If scheduled but no date provided, default to now or keep it draft.
            // Best to throw validation error but since it passed, we will set it to draft
            $validated['status'] = 'draft';
        }

        $blog = Blog::create($validated);

        return new BlogResource($blog->load('author:id,name,avatar_path'));
    }

    public function show(string $slugOrId)
    {
        $query = Blog::with('author:id,name,avatar_path')
            ->where(function ($q) {
                $q->where('status', 'published')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'scheduled')
                         ->where('published_at', '<=', now());
                  });
            });

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

        $rules = [
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'status' => 'sometimes|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'focus_keyword' => 'nullable|string|max:100',
            'seo_title' => 'nullable|string|max:70',
            'seo_description' => 'nullable|string|max:200',
            'seo_keywords' => 'nullable',
            'canonical_url' => 'nullable|url|max:255',
            'twitter_card' => 'nullable|in:summary,summary_large_image',
        ];

        if ($request->hasFile('featured_image')) {
            $rules['featured_image'] = 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240';
        } else {
            $rules['featured_image'] = 'nullable|string';
        }

        if ($request->hasFile('og_image')) {
            $rules['og_image'] = 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:10240';
        } else {
            $rules['og_image'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        if (array_key_exists('seo_keywords', $validated)) {
            if (is_string($validated['seo_keywords'])) {
                $decoded = json_decode($validated['seo_keywords'], true);
                $validated['seo_keywords'] = is_array($decoded) ? array_values(array_filter(array_map('trim', $decoded))) : [];
            } elseif (!is_array($validated['seo_keywords'])) {
                $validated['seo_keywords'] = [];
            }
        }

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $teamStr = $request->user()->team ? $request->user()->team->slug : 'superadmin';
            $path = $file->storeAs(
                $teamStr . '/blog/' . date('Y/m'),
                time() . '_' . $file->getClientOriginalName(),
                'r2'
            );
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('r2');
            $validated['featured_image'] = $disk->url($path);
        }

        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $teamStr = $request->user()->team ? $request->user()->team->slug : 'superadmin';
            $path = $file->storeAs(
                $teamStr . '/blog/og/' . date('Y/m'),
                time() . '_' . $file->getClientOriginalName(),
                'r2'
            );
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('r2');
            $validated['og_image'] = $disk->url($path);
        }

        if (isset($validated['status']) && $validated['status'] === 'published' && empty($blog->published_at)) {
            $validated['published_at'] = now();
        } else if (isset($validated['status']) && $validated['status'] === 'scheduled' && empty($validated['published_at']) && empty($blog->published_at)) {
            $validated['status'] = 'draft';
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

        $query = Blog::with('author:id,name,avatar_path');

        if ($user->isSuperAdmin()) {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->where('team_id', $user->team_id)
                ->orderBy('created_at', 'desc');
        }

        return BlogResource::collection($query->paginate(15));
    }
}
