<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockImport;
use App\Services\StockImportService;
use Illuminate\Http\Request;

class StockImportController extends Controller
{
    public function __construct(private readonly StockImportService $service)
    {
    }

    /** Geçmiş içe aktarımları listeler. */
    public function index(Request $request)
    {
        return StockImport::with('importedBy')->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'mode' => 'required|in:upsert,replace',
            'confirm' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        $result = $this->service->process(
            file: $data['file'],
            mode: $data['mode'],
            company: $user->company,
            user: $user,
            confirm: (bool) ($data['confirm'] ?? false)
        );

        return match ($result['status']) {
            'format_error' => response()->json(['message' => $result['message']], 422),
            'confirmation_required' => response()->json($result, 409),
            'completed' => response()->json($result, 201),
        };
    }
}
