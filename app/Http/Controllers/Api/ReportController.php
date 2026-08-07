<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function dashboard(): JsonResponse
    {
        return response()->json($this->reportService->dashboard());
    }

    public function production(Request $request): JsonResponse
    {
        return response()->json($this->reportService->production($request->all()));
    }

    public function inventory(Request $request): JsonResponse
    {
        return response()->json($this->reportService->inventory($request->all()));
    }

    public function sales(Request $request): JsonResponse
    {
        return response()->json($this->reportService->sales($request->all()));
    }

    public function quality(Request $request): JsonResponse
    {
        return response()->json($this->reportService->quality($request->all()));
    }

    public function purchase(Request $request): JsonResponse
    {
        return response()->json($this->reportService->purchase($request->all()));
    }

    public function export(Request $request): JsonResponse
    {
        $type = $request->input('type', 'production');
        return response()->json($this->reportService->export($type, $request->all()));
    }
}
