<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportRoute;
use App\Models\TransportRouteStop;
use App\Models\TransportAllocation;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportController extends Controller
{
    public function dashboard()
    {
        $totalRoutes = TransportRoute::count();
        $activeRoutes = TransportRoute::where('status', 'active')->count();
        $totalAllocations = TransportAllocation::where('status', 'active')->count();
        $totalStops = TransportRouteStop::count();
        
        $routes = TransportRoute::withCount('stops')->withCount(['allocations' => function($q) {
            $q->where('status', 'active');
        }])->get();
        
        return view('admin.transport.dashboard', compact('totalRoutes', 'activeRoutes', 'totalAllocations', 'totalStops', 'routes'));
    }

    public function routes()
    {
        $routes = TransportRoute::withCount('stops')->withCount(['allocations' => function($q) {
            $q->where('status', 'active');
        }])->get();
        return view('admin.transport.routes', compact('routes'));
    }

    public function storeRoute(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'start_point' => 'required|string|max:255',
            'end_point' => 'required|string|max:255',
            'default_monthly_fee' => 'required|numeric|min:0',
        ]);

        TransportRoute::create($request->all());
        return back()->with('success', 'Route created successfully!');
    }

    public function updateRoute(Request $request, $id)
    {
        $route = TransportRoute::findOrFail($id);
        $request->validate([
            'route_name' => 'required|string|max:255',
            'start_point' => 'required|string|max:255',
            'end_point' => 'required|string|max:255',
            'default_monthly_fee' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $route->update($request->all());
        return back()->with('success', 'Route updated successfully!');
    }

    public function destroyRoute($id)
    {
        $route = TransportRoute::findOrFail($id);
        if ($route->allocations()->exists()) {
            return back()->with('error', 'Cannot delete route with existing allocations.');
        }
        $route->delete();
        return back()->with('success', 'Route deleted successfully!');
    }

    public function stops($route_id)
    {
        $route = TransportRoute::with('stops')->findOrFail($route_id);
        return view('admin.transport.stops', compact('route'));
    }

    public function storeStop(Request $request, $route_id)
    {
        $route = TransportRoute::findOrFail($route_id);
        $request->validate([
            'stop_name' => 'required|string|max:255',
            'stop_order' => 'required|integer|min:1',
            'pickup_time' => 'nullable',
            'drop_time' => 'nullable',
            'additional_fee' => 'required|numeric|min:0',
        ]);

        $route->stops()->create($request->all());
        return back()->with('success', 'Stop added successfully!');
    }

    public function updateStop(Request $request, $id)
    {
        $stop = TransportRouteStop::findOrFail($id);
        $request->validate([
            'stop_name' => 'required|string|max:255',
            'stop_order' => 'required|integer|min:1',
            'pickup_time' => 'nullable',
            'drop_time' => 'nullable',
            'additional_fee' => 'required|numeric|min:0',
        ]);

        $stop->update($request->all());
        return back()->with('success', 'Stop updated successfully!');
    }

    public function destroyStop($id)
    {
        $stop = TransportRouteStop::findOrFail($id);
        $stop->delete();
        return back()->with('success', 'Stop deleted successfully!');
    }

    public function getRouteStops($route_id)
    {
        $stops = TransportRouteStop::where('route_id', $route_id)->orderBy('stop_order')->get();
        return response()->json($stops);
    }
    
    public function allocations()
    {
        $allocations = TransportAllocation::with(['studentProfile.user', 'route', 'stop'])
            ->orderBy('id', 'desc')
            ->get();
        
        // Load active students who do not have an active transport allocation
        $students = StudentProfile::where('status', 'active')
            ->whereDoesntHave('transportAllocations', function($q) {
                $q->where('status', 'active');
            })
            ->with('user')
            ->get();
            
        // Load active transport routes
        $routes = TransportRoute::where('status', 'active')->get();

        return view('admin.transport.allocations', compact('allocations', 'students', 'routes'));
    }

    public function allocateRoute(Request $request)
    {
        $request->validate([
            'student_profile_ids' => 'required|array',
            'student_profile_ids.*' => 'exists:student_profiles,id',
            'route_id' => 'required|exists:transport_routes,id',
            'stop_id' => 'nullable|exists:transport_route_stops,id',
            'monthly_fee' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $routeId = $request->route_id;
        $stopId = $request->stop_id;
        $fee = $request->monthly_fee;
        $effectiveFrom = $request->effective_from;

        DB::transaction(function () use ($request, $routeId, $stopId, $fee, $effectiveFrom) {
            foreach ($request->student_profile_ids as $studentId) {
                $student = StudentProfile::findOrFail($studentId);

                // Skip if already active to avoid duplicate allocations
                $existing = TransportAllocation::where('student_profile_id', $student->id)
                    ->where('status', 'active')
                    ->first();

                if ($existing) {
                    continue;
                }

                // Create allocation
                TransportAllocation::create([
                    'student_profile_id' => $student->id,
                    'route_id' => $routeId,
                    'stop_id' => $stopId,
                    'monthly_fee' => $fee,
                    'effective_from' => $effectiveFrom,
                    'status' => 'active',
                ]);

                // Create history log
                \App\Models\TransportHistory::create([
                    'student_profile_id' => $student->id,
                    'route_id' => $routeId,
                    'stop_id' => $stopId,
                    'action' => 'Allocated',
                    'new_value' => 'Status: Active, Fee: ' . $fee,
                    'action_date' => $effectiveFrom,
                    'reason' => 'Allocated via Allocations page modal',
                    'performed_by' => auth()->id(),
                ]);

                // Ensure Transport Fee Structure exists
                $feeCat = \App\Models\FeeCategory::firstOrCreate(
                    ['name' => 'Transport Fee'],
                    ['type' => 'monthly', 'installments_count' => 12]
                );

                \App\Models\FeeStructure::firstOrCreate(
                    [
                        'class_id' => $student->class_id,
                        'fee_category_id' => $feeCat->id,
                    ],
                    [
                        'amount' => $fee,
                    ]
                );
            }
        });

        return back()->with('success', 'Transport route allocated successfully to selected students!');
    }

    public function updateAllocation(Request $request, $id)
    {
        $allocation = TransportAllocation::findOrFail($id);
        $student = \App\Models\StudentProfile::findOrFail($allocation->student_profile_id);

        // If this is just a status toggle (like release/reactivate)
        if ($request->has('status') && !$request->has('route_id')) {
            $request->validate([
                'status' => 'required|in:active,cancelled',
            ]);

            $newStatus = $request->status;

            if ($newStatus === 'cancelled' && $allocation->status === 'active') {
                if ($this->checkTransportDue($student)) {
                    return back()->with('error', 'Cannot cancel transport allocation. The student has pending transport dues up to the current month. Please clear all dues first.');
                }
            }

            DB::transaction(function () use ($allocation, $student, $newStatus) {
                if ($newStatus === 'cancelled' && $allocation->status === 'active') {
                    $allocation->update([
                        'status' => 'cancelled',
                        'effective_to' => date('Y-m-d')
                    ]);

                    \App\Models\TransportHistory::create([
                        'student_profile_id' => $student->id,
                        'route_id' => $allocation->route_id,
                        'stop_id' => $allocation->stop_id,
                        'action' => 'Cancelled',
                        'new_value' => 'Status: Cancelled',
                        'action_date' => date('Y-m-d'),
                        'reason' => 'Transport cancelled/released via Transport Allocations',
                        'performed_by' => auth()->id(),
                    ]);
                } else if ($newStatus === 'active' && $allocation->status === 'cancelled') {
                    $allocation->update([
                        'status' => 'active',
                        'effective_to' => null
                    ]);

                    \App\Models\TransportHistory::create([
                        'student_profile_id' => $student->id,
                        'route_id' => $allocation->route_id,
                        'stop_id' => $allocation->stop_id,
                        'action' => 'Re-activated',
                        'new_value' => 'Status: Active',
                        'action_date' => date('Y-m-d'),
                        'reason' => 'Transport re-activated via Transport Allocations',
                        'performed_by' => auth()->id(),
                    ]);
                }
            });

            return back()->with('success', 'Allocation status updated successfully!');
        }

        // Otherwise, this is a full edit of route/stop/fee/effective_from
        $request->validate([
            'route_id' => 'required|exists:transport_routes,id',
            'stop_id' => 'nullable|exists:transport_route_stops,id',
            'monthly_fee' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $newRouteId = $request->route_id;
        $newStopId = $request->stop_id;
        $fee = $request->monthly_fee;
        $effectiveFrom = $request->effective_from;

        $dateChanged = $allocation->effective_from ? $allocation->effective_from->format('Y-m-d') != $effectiveFrom : true;

        if ($allocation->route_id != $newRouteId || $allocation->stop_id != $newStopId || $allocation->monthly_fee != $fee || $dateChanged) {
            // Check dues if changing route/stop/fee
            if ($allocation->route_id != $newRouteId || $allocation->stop_id != $newStopId || $allocation->monthly_fee != $fee) {
                if ($this->checkTransportDue($student)) {
                    return back()->with('error', 'Cannot change transport allocation. The student has pending transport dues up to the current month. Please clear all dues first.');
                }
            }

            DB::transaction(function () use ($allocation, $student, $newRouteId, $newStopId, $fee, $effectiveFrom, $dateChanged) {
                $routeChanged = $allocation->route_id != $newRouteId || $allocation->stop_id != $newStopId || $allocation->monthly_fee != $fee;

                if ($routeChanged) {
                    // Deactivate the current active allocation by setting effective_to
                    $allocation->update([
                        'status' => 'cancelled',
                        'effective_to' => date('Y-m-d', strtotime('-1 day', strtotime($effectiveFrom)))
                    ]);

                    // Log the Change in History
                    \App\Models\TransportHistory::create([
                        'student_profile_id' => $student->id,
                        'route_id' => $allocation->route_id,
                        'stop_id' => $allocation->stop_id,
                        'action' => 'Changed',
                        'old_value' => 'Route: ' . $allocation->route_id,
                        'new_value' => 'Route: ' . $newRouteId,
                        'action_date' => $effectiveFrom,
                        'reason' => 'Route changed via allocations edit modal',
                        'performed_by' => auth()->id(),
                    ]);

                    // Create a new active allocation record
                    TransportAllocation::create([
                        'student_profile_id' => $student->id,
                        'route_id' => $newRouteId,
                        'stop_id' => $newStopId,
                        'monthly_fee' => $fee,
                        'effective_from' => $effectiveFrom,
                        'status' => 'active',
                    ]);
                } else {
                    // Only date changed
                    $oldDate = $allocation->effective_from ? $allocation->effective_from->format('Y-m-d') : 'N/A';
                    $allocation->update([
                        'effective_from' => $effectiveFrom
                    ]);

                    // Log the Change in History
                    \App\Models\TransportHistory::create([
                        'student_profile_id' => $student->id,
                        'route_id' => $allocation->route_id,
                        'stop_id' => $allocation->stop_id,
                        'action' => 'Changed',
                        'old_value' => 'Effective Date: ' . $oldDate,
                        'new_value' => 'Effective Date: ' . $effectiveFrom,
                        'action_date' => $effectiveFrom,
                        'reason' => 'Effective date changed via allocations edit modal',
                        'performed_by' => auth()->id(),
                    ]);
                }

                // Ensure Transport Fee Structure exists
                $feeCat = \App\Models\FeeCategory::firstOrCreate(
                    ['name' => 'Transport Fee'],
                    ['type' => 'monthly', 'installments_count' => 12]
                );

                \App\Models\FeeStructure::firstOrCreate(
                    [
                        'class_id' => $student->class_id,
                        'fee_category_id' => $feeCat->id,
                    ],
                    [
                        'amount' => $fee,
                    ]
                );
            });

            return back()->with('success', 'Transport allocation updated successfully!');
        }

        return back()->with('success', 'No changes were made.');
    }

    public function releaseAllocation($id)
    {
        $allocation = TransportAllocation::findOrFail($id);
        $student = \App\Models\StudentProfile::find($allocation->student_profile_id);

        if ($this->checkTransportDue($student)) {
            return back()->with('error', 'Cannot release transport allocation. The student has pending transport dues up to the current month. Please clear all dues first.');
        }

        DB::transaction(function () use ($allocation, $student) {
            $allocation->update([
                'status' => 'cancelled',
                'effective_to' => date('Y-m-d')
            ]);

            \App\Models\TransportHistory::create([
                'student_profile_id' => $student->id,
                'route_id' => $allocation->route_id,
                'stop_id' => $allocation->stop_id,
                'action' => 'Cancelled',
                'new_value' => 'Status: Cancelled',
                'action_date' => date('Y-m-d'),
                'reason' => 'Transport released via Release button',
                'performed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Transport allocation released successfully!');
    }

    public function history()
    {
        $history = \App\Models\TransportHistory::with(['studentProfile.user', 'route', 'stop', 'user'])
            ->orderBy('action_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.transport.history', compact('history'));
    }

    private function checkTransportDue($student)
    {
        $transportCategory = \App\Models\FeeCategory::where('name', 'Transport Fee')->first();
        if (!$transportCategory) return false;
        
        foreach ($student->invoices as $invoice) {
            if ($invoice->status !== 'paid' && $invoice->status !== 'refunded') {
                foreach ($invoice->items as $item) {
                    if ($item->fee_category_id == $transportCategory->id) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}
