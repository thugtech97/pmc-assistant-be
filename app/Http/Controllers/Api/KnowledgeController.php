<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Knowledge;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    /**
     * GET /api/knowledge?type=camm
     */
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string',
        ]);

        $query = Knowledge::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->orderByDesc('updated_at')->get();

        return response()->json($items);
    }

    /**
     * POST /api/knowledge
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|string',
            'content' => 'required|string',
            'faqs'    => 'nullable|array',
            'faqs.*'  => 'nullable|string',
        ]);

        // Strip empty FAQ strings
        $data['faqs'] = array_values(array_filter($data['faqs'] ?? [], fn($f) => trim($f) !== ''));

        $item = Knowledge::create($data);

        return response()->json($item, 201);
    }

    /**
     * GET /api/knowledge/{id}
     */
    public function show(Knowledge $knowledge)
    {
        return response()->json($knowledge);
    }

    /**
     * PUT /api/knowledge/{id}
     */
    public function update(Request $request, Knowledge $knowledge)
    {
        $data = $request->validate([
            'type'    => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'faqs'    => 'nullable|array',
            'faqs.*'  => 'nullable|string',
        ]);

        if (array_key_exists('faqs', $data)) {
            $data['faqs'] = array_values(array_filter($data['faqs'] ?? [], fn($f) => trim($f) !== ''));
        }

        $knowledge->update($data);

        return response()->json($knowledge);
    }

    /**
     * DELETE /api/knowledge/{id}
     */
    public function destroy(Knowledge $knowledge)
    {
        $knowledge->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    public function types()
    {
        $types = Knowledge::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return response()->json($types);
    }
}