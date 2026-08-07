<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQualityCheckRequest;
use App\Models\Defect;
use App\Models\QualityCheck;
use App\Models\QualityCheckItem;
use App\Services\QualityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    public function __construct(protected QualityService $qualityService) {}

    public function checks(): JsonResponse
    {
        $checks = QualityCheck::with('batch.order.product', 'inspector', 'items')->latest()->paginate(20);
        return response()->json($checks);
    }

    public function storeCheck(StoreQualityCheckRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['check_date'] = $data['check_date'] ?? now()->toDateString();

        if (empty($data['inspector_id']) && !empty($data['inspector'])) {
            $name = $data['inspector'];
            $user = \App\Models\User::where('name', $name)->first()
                ?? \App\Models\User::whereRaw('LOWER(name) = ?', [strtolower($name)])->first()
                ?? \App\Models\User::whereRaw('LOWER(name) LIKE ?', [strtolower($name) . '%'])->first()
                ?? \App\Models\User::whereRaw('LOWER(name) LIKE ?', ['% ' . strtolower($name) . '%'])->first();
            $data['inspector_id'] = $user?->id;
        }
        if (empty($data['inspector_id'])) {
            $data['inspector_id'] = auth()->id();
        }
        unset($data['inspector']);

        if (!empty($data['result']) && empty($data['status'])) {
            $data['status'] = match (strtolower($data['result'])) {
                'pass', 'passed' => 'passed',
                'fail', 'failed' => 'failed',
                default => 'pending',
            };
        }
        unset($data['result']);

        $check = $this->qualityService->createCheck($data);
        return response()->json($check->load('batch.order.product', 'inspector', 'items'), 201);
    }

    public function showCheck(QualityCheck $qualityCheck): JsonResponse
    {
        $qualityCheck->load('batch.order.product', 'inspector', 'items');
        return response()->json($qualityCheck);
    }

    public function updateCheckItem(Request $request, QualityCheckItem $qualityCheckItem): JsonResponse
    {
        $item = $this->qualityService->updateCheckItem($qualityCheckItem, $request->validate([
            'actual_value' => 'nullable|string',
            'status' => 'required|string|in:pending,passed,failed',
        ]));
        return response()->json($item);
    }

    public function defects(): JsonResponse
    {
        $defects = Defect::with('batch.order.product', 'resolver')->latest()->paginate(20);
        return response()->json($defects);
    }

    public function storeDefect(Request $request): JsonResponse
    {
        $defect = $this->qualityService->recordDefect($request->validate([
            'batch_id' => 'required|exists:production_batches,id',
            'type' => 'required|string|max:255',
            'severity' => 'nullable|string|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
        ]));
        return response()->json($defect, 201);
    }

    public function resolveDefect(Request $request, Defect $defect): JsonResponse
    {
        $defect->update([
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);
        return response()->json($defect);
    }

    public function updateDefect(Request $request, Defect $defect): JsonResponse
    {
        $defect->update($request->validate([
            'batch_id' => 'nullable|exists:production_batches,id',
            'type' => 'nullable|string|max:255',
            'severity' => 'nullable|string|in:minor,low,medium,high,critical',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
        ]));
        return response()->json($defect);
    }

    public function destroyDefect(Defect $defect): JsonResponse
    {
        $defect->delete();
        return response()->json(['message' => 'Defect deleted']);
    }

    public function destroyCheck(QualityCheck $qualityCheck): JsonResponse
    {
        $qualityCheck->delete();
        return response()->json(['message' => 'Quality check deleted']);
    }
}
