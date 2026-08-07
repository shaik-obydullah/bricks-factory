<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function employees(Request $request): JsonResponse
    {
        $employees = Employee::with('shift')
            ->when($request->input('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(20);
        return response()->json($employees);
    }

    public function storeEmployee(Request $request): JsonResponse
    {
        $employee = Employee::create($request->validate([
            'employee_id' => 'required|string|max:50|unique:employees,employee_id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'shift_id' => 'nullable|exists:shifts,id',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($employee, 201);
    }

    public function showEmployee(Employee $employee): JsonResponse
    {
        $employee->load('shift', 'attendance');
        return response()->json($employee);
    }

    public function updateEmployee(Request $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'shift_id' => 'nullable|exists:shifts,id',
            'joining_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($employee);
    }

    public function destroyEmployee(Employee $employee): JsonResponse
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted']);
    }

    public function attendance(Request $request): JsonResponse
    {
        $attendance = Attendance::with('employee')
            ->when($request->input('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->input('date_to'));
            })
            ->latest()
            ->paginate(20);
        return response()->json($attendance);
    }

    public function storeAttendance(Request $request): JsonResponse
    {
        $attendance = Attendance::create($request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'nullable|string|in:present,absent,late,half_day',
            'notes' => 'nullable|string',
        ]));
        return response()->json($attendance, 201);
    }
}
