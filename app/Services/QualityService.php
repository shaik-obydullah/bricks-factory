<?php

namespace App\Services;

use App\Models\Defect;
use App\Models\QualityCheck;
use App\Models\QualityCheckItem;

class QualityService
{
    public function createCheck(array $data): QualityCheck
    {
        $data['status'] = $data['status'] ?? 'pending';
        return QualityCheck::create($data);
    }

    public function updateCheckItem(QualityCheckItem $item, array $data): QualityCheckItem
    {
        $item->update($data);

        $check = $item->check;
        $allItems = $check->items;

        if ($allItems->count() > 0 && $allItems->every(fn($i) => $i->status === 'passed')) {
            $check->update(['status' => 'passed']);
        } elseif ($allItems->contains(fn($i) => $i->status === 'failed')) {
            $check->update(['status' => 'failed']);
        }

        return $item->fresh();
    }

    public function recordDefect(array $data): Defect
    {
        $data['status'] = $data['status'] ?? 'open';
        return Defect::create($data);
    }

    public function getQualityReport(array $filters = []): array
    {
        $query = QualityCheck::with(['batch.order.product', 'items', 'inspector']);

        if (!empty($filters['from'])) {
            $query->where('check_date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('check_date', '<=', $filters['to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $checks = $query->get();

        return [
            'total_checks' => $checks->count(),
            'passed' => $checks->where('status', 'passed')->count(),
            'failed' => $checks->where('status', 'failed')->count(),
            'pending' => $checks->where('status', 'pending')->count(),
            'checks' => $checks,
        ];
    }
}
