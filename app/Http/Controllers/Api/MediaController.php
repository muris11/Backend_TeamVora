<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMediaResource;
use App\Models\TeamMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function documents(Request $request)
    {
        $query = TeamMedia::with('user:id,name')
            ->where('type', 'document');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        $documents = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return TeamMediaResource::collection($documents);
    }

    public function gallery(Request $request)
    {
        $query = TeamMedia::with('user:id,name')
            ->where('type', 'gallery');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        $photos = $query->orderBy('created_at', 'desc')
            ->paginate(24);

        return TeamMediaResource::collection($photos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:document,gallery',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->storeAs(
            $request->type === 'gallery' ? 'gallery/' . date('Y/m') : 'documents',
            time() . '_' . $file->getClientOriginalName(),
            's3'
        );

        $media = TeamMedia::create([
            'user_id' => $request->user()->id,
            'team_id' => $request->user()->team_id,
            'type' => $request->type,
            'name' => $request->name,
            'file_path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return new TeamMediaResource($media->load('user'));
    }

    public function destroy(Request $request, TeamMedia $media)
    {
        $isAdmin = $request->user()->hasRole('Admin');
        if (! $isAdmin && $request->user()->id !== $media->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $path = $media->file_path;
        // If stored as full URL, extract path
        $s3Url = config('filesystems.disks.s3.url');
        if ($s3Url && str_starts_with($path, $s3Url)) {
            $path = str_replace($s3Url . '/', '', $path);
        }

        if ($path) {
            Storage::disk('s3')->delete($path);
        }

        $media->delete();
        return response()->json(['message' => 'File dihapus.']);
    }
}
