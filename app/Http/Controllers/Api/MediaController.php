<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMediaResource;
use App\Models\TeamMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'file' => [
                'required', 'file', 'max:10240',
                Rule::when(
                    $request->type === 'gallery',
                    ['mimes:jpeg,jpg,png,gif,webp,svg'],
                    ['mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip']
                ),
            ],
        ]);


        $file = $request->file('file');
        $path = $file->storeAs(
            $request->type === 'gallery' ? 'gallery/' . date('Y/m') : 'documents',
            time() . '_' . $file->getClientOriginalName(),
            'r2'
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
        $isAdmin = $request->user()->hasRole('super_admin');
        if (! $isAdmin && $request->user()->id !== $media->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $path = $media->file_path;
        // Strip CDN domain prefix if file_path was stored as full URL
        $r2Url = config('filesystems.disks.r2.url');
        if ($r2Url && str_starts_with($path, $r2Url)) {
            $path = ltrim(str_replace($r2Url, '', $path), '/');
        }

        if ($path) {
            try {
                Storage::disk('r2')->delete($path);
            } catch (\Throwable $e) {
                // Log but don't block the delete if the file is already gone
                logger()->warning('R2 delete failed for key [' . $path . ']: ' . $e->getMessage());
            }
        }

        $media->delete();
        return response()->json(['message' => 'File dihapus.']);
    }
}
