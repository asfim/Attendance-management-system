<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\Bed;
use App\Models\HostelAllocation;
use App\Models\StudentProfile;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HostelController extends Controller
{
    // ==========================================
    // 1. HALLS / HOUSES MANAGEMENT
    // ==========================================
    public function hallsIndex()
    {
        $hostels = Hostel::withCount(['rooms', 'rooms as beds_count' => function ($q) {
            $q->join('beds', 'rooms.id', '=', 'beds.room_id');
        }])->orderBy('name', 'asc')->get();

        return view('admin.hostel.halls', compact('hostels'));
    }

    public function hallStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:hostels,name',
            'type' => 'required|in:Boys,Girls,Combined',
            'address' => 'nullable|string',
        ]);

        Hostel::create([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.hostel.halls.index')->with('success', 'Hostel / Hall created successfully!');
    }

    public function hallUpdate(Request $request, $id)
    {
        $hostel = Hostel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:hostels,name,'.$id,
            'type' => 'required|in:Boys,Girls,Combined',
            'address' => 'nullable|string',
        ]);

        $hostel->update([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.hostel.halls.index')->with('success', 'Hostel / Hall updated successfully!');
    }

    public function hallDestroy($id)
    {
        $hostel = Hostel::findOrFail($id);

        if ($hostel->rooms()->exists()) {
            return redirect()->route('admin.hostel.halls.index')->with('error', 'Cannot delete hostel because it has rooms assigned to it.');
        }

        $hostel->delete();

        return redirect()->route('admin.hostel.halls.index')->with('success', 'Hostel / Hall deleted successfully!');
    }

    // ==========================================
    // 2. ROOMS MANAGEMENT
    // ==========================================
    public function roomsIndex()
    {
        $hostels = Hostel::all();
        $rooms = Room::with(['hostel', 'beds'])->withCount([
            'beds as total_beds_count',
            'beds as available_beds_count' => function ($q) {
                $q->where('status', 'available');
            }
        ])->orderBy('room_number', 'asc')->get();

        return view('admin.hostel.rooms', compact('hostels', 'rooms'));
    }

    public function roomStore(Request $request)
    {
        $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string|max:50',
            'room_type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'cost_per_bed' => 'required|numeric|min:0',
        ]);

        // Check room_number uniqueness within the same hostel
        $exists = Room::where('hostel_id', $request->hostel_id)
            ->where('room_number', $request->room_number)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Room number already exists in this hostel.');
        }

        DB::transaction(function () use ($request) {
            $room = Room::create([
                'hostel_id' => $request->hostel_id,
                'room_number' => $request->room_number,
                'room_type' => $request->room_type,
                'capacity' => $request->capacity,
                'cost_per_bed' => $request->cost_per_bed,
            ]);

            // Auto generate beds for the room up to capacity
            for ($i = 1; $i <= $request->capacity; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => 'Bed ' . $i,
                    'status' => 'available',
                ]);
            }
        });

        return redirect()->route('admin.hostel.rooms.index')->with('success', 'Room created and beds generated successfully!');
    }

    public function roomUpdate(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string|max:50',
            'room_type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'cost_per_bed' => 'required|numeric|min:0',
        ]);

        $exists = Room::where('hostel_id', $request->hostel_id)
            ->where('room_number', $request->room_number)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Room number already exists in this hostel.');
        }

        $room->update([
            'hostel_id' => $request->hostel_id,
            'room_number' => $request->room_number,
            'room_type' => $request->room_type,
            'capacity' => $request->capacity,
            'cost_per_bed' => $request->cost_per_bed,
        ]);

        return redirect()->route('admin.hostel.rooms.index')->with('success', 'Room updated successfully!');
    }

    public function roomDestroy($id)
    {
        $room = Room::findOrFail($id);

        // Check if any bed in this room is occupied
        if ($room->beds()->where('status', 'occupied')->exists()) {
            return redirect()->route('admin.hostel.rooms.index')->with('error', 'Cannot delete room because it has occupied beds.');
        }

        DB::transaction(function () use ($room) {
            $room->beds()->delete();
            $room->delete();
        });

        return redirect()->route('admin.hostel.rooms.index')->with('success', 'Room and beds deleted successfully!');
    }

    // ==========================================
    // 3. BEDS MANAGEMENT
    // ==========================================
    public function bedsIndex()
    {
        $rooms = Room::with('hostel')->orderBy('room_number', 'asc')->get();
        $beds = Bed::with(['room.hostel', 'allocations.studentProfile.user'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.hostel.beds', compact('rooms', 'beds'));
    }

    public function bedStore(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:50',
        ]);

        $exists = Bed::where('room_id', $request->room_id)
            ->where('bed_number', $request->bed_number)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Bed number already exists in this room.');
        }

        Bed::create([
            'room_id' => $request->room_id,
            'bed_number' => $request->bed_number,
            'status' => 'available',
        ]);

        return redirect()->route('admin.hostel.beds.index')->with('success', 'Bed created successfully!');
    }

    public function bedUpdate(Request $request, $id)
    {
        $bed = Bed::findOrFail($id);

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:50',
            'status' => 'required|in:available,occupied',
        ]);

        $bed->update([
            'room_id' => $request->room_id,
            'bed_number' => $request->bed_number,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.hostel.beds.index')->with('success', 'Bed updated successfully!');
    }

    public function bedDestroy($id)
    {
        $bed = Bed::findOrFail($id);

        if ($bed->status === 'occupied') {
            return redirect()->route('admin.hostel.beds.index')->with('error', 'Cannot delete an occupied bed.');
        }

        $bed->delete();

        return redirect()->route('admin.hostel.beds.index')->with('success', 'Bed deleted successfully!');
    }

    // ==========================================
    // 4. ALLOCATIONS MANAGEMENT
    // ==========================================
    public function allocationsIndex()
    {
        $hostels = Hostel::all();
        $students = StudentProfile::with('user')
            ->where('status', 'active')
            ->whereDoesntHave('hostelAllocations', function ($q) {
                $q->where('status', 'active');
            })->get();

        $allocations = HostelAllocation::with(['studentProfile.user', 'bed.room.hostel'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.hostel.allocations', compact('hostels', 'students', 'allocations'));
    }

    public function allocateBed(Request $request)
    {
        $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'bed_id' => 'required|exists:beds,id',
            'allocation_date' => 'required|date',
        ]);

        $existingAllocation = HostelAllocation::where('student_profile_id', $request->student_profile_id)
            ->where('status', 'active')
            ->first();

        if ($existingAllocation) {
            return back()->with('error', 'Student is already allocated to a bed. Please release them first before re-allocating.');
        }

        $bed = Bed::with('room')->findOrFail($request->bed_id);
        if ($bed->status !== 'available') {
            return back()->with('error', 'Selected bed is not available.');
        }

        DB::transaction(function () use ($request, $bed) {
            HostelAllocation::create([
                'student_profile_id' => $request->student_profile_id,
                'bed_id' => $request->bed_id,
                'allocation_date' => $request->allocation_date,
                'status' => 'active',
            ]);

            $bed->update(['status' => 'occupied']);

            // Integrate with Fee System if Hostel Fee Category exists
            $student = StudentProfile::find($request->student_profile_id);
            if ($student && $bed->room) {
                $feeCat = FeeCategory::firstOrCreate(
                    ['name' => 'Hostel Fee']
                );

                FeeStructure::firstOrCreate(
                    [
                        'class_id' => $student->class_id,
                        'fee_category_id' => $feeCat->id,
                    ],
                    [
                        'amount' => $bed->room->cost_per_bed,
                    ]
                );
            }
        });

        return redirect()->route('admin.hostel.allocations.index')->with('success', 'Hostel bed allocated successfully!');
    }

    public function deallocateBed($id)
    {
        $allocation = HostelAllocation::findOrFail($id);
        $student = \App\Models\StudentProfile::find($allocation->student_profile_id);

        if ($student && $this->checkHostelDue($student)) {
            return back()->with('error', 'Cannot deallocate bed. The student has pending hostel dues up to the current month. Please clear all dues first.');
        }

        DB::transaction(function () use ($allocation) {
            if ($allocation->bed) {
                $allocation->bed->update(['status' => 'available']);
            }
            $allocation->update(['status' => 'released']);
        });

        return redirect()->route('admin.hostel.allocations.index')->with('success', 'Bed deallocated successfully!');
    }

    private function checkHostelDue($student)
    {
        $hostelCategory = \App\Models\FeeCategory::where('name', 'Hostel Fee')->first();
        if (!$hostelCategory) return false;
        
        foreach ($student->invoices as $invoice) {
            if ($invoice->status !== 'paid' && $invoice->status !== 'refunded') {
                foreach ($invoice->items as $item) {
                    if ($item->fee_category_id == $hostelCategory->id) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    public function updateAllocation(Request $request, $id)
    {
        $allocation = HostelAllocation::findOrFail($id);

        $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'bed_id' => 'required|exists:beds,id',
            'allocation_date' => 'required|date',
            'status' => 'required|in:active,released',
        ]);

        $oldBedId = $allocation->bed_id;
        $newBedId = $request->bed_id;
        $newStatus = $request->status;
        
        $student = StudentProfile::find($request->student_profile_id);
        if ($newStatus === 'released' && $allocation->status === 'active') {
            if ($student && $this->checkHostelDue($student)) {
                return back()->with('error', 'Cannot change status to released. The student has pending hostel dues up to the current month. Please clear all dues first.');
            }
        }

        DB::transaction(function () use ($allocation, $request, $oldBedId, $newBedId, $newStatus) {
            $allocation->update([
                'student_profile_id' => $request->student_profile_id,
                'bed_id' => $newBedId,
                'allocation_date' => $request->allocation_date,
                'status' => $newStatus,
            ]);

            // If the bed has changed
            if ($oldBedId != $newBedId) {
                // Free the old bed
                $oldBed = Bed::find($oldBedId);
                if ($oldBed) {
                    $oldBed->update(['status' => 'available']);
                }

                // Occupy the new bed (if active)
                if ($newStatus === 'active') {
                    $newBed = Bed::find($newBedId);
                    if ($newBed) {
                        $newBed->update(['status' => 'occupied']);
                    }
                }
            } else {
                // If status changed
                $bed = Bed::find($newBedId);
                if ($bed) {
                    if ($newStatus === 'released') {
                        $bed->update(['status' => 'available']);
                    } else {
                        $bed->update(['status' => 'occupied']);
                    }
                }
            }

            // Sync with Fee System if active
            if ($newStatus === 'active') {
                $student = StudentProfile::find($request->student_profile_id);
                $newBed = Bed::with('room')->find($newBedId);
                if ($student && $newBed && $newBed->room) {
                    $feeCat = FeeCategory::firstOrCreate(
                        ['name' => 'Hostel Fee']
                    );

                    FeeStructure::firstOrCreate(
                        [
                            'class_id' => $student->class_id,
                            'fee_category_id' => $feeCat->id,
                        ],
                        [
                            'amount' => $newBed->room->cost_per_bed,
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.hostel.allocations.index')->with('success', 'Hostel allocation updated successfully!');
    }

    // ==========================================
    // 5. HOSTEL REPORT SUB-MENU
    // ==========================================
    public function report()
    {
        $hostels = Hostel::with(['rooms.beds' => function ($q) {
            $q->orderBy('bed_number', 'asc');
        }])->get();

        $totalHalls = $hostels->count();
        $totalRooms = Room::count();
        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();
        $availableBeds = Bed::where('status', 'available')->count();

        // Count available rooms (rooms having at least 1 available bed)
        $availableRooms = Room::whereHas('beds', function ($q) {
            $q->where('status', 'available');
        })->count();

        return view('admin.hostel.report', compact(
            'hostels',
            'totalHalls',
            'totalRooms',
            'totalBeds',
            'occupiedBeds',
            'availableBeds',
            'availableRooms'
        ));
    }

    // ==========================================
    // AJAX ENDPOINTS FOR DYNAMIC FILTERING
    // ==========================================

    /**
     * Get rooms under a hostel that have AT LEAST 1 available bed.
     * Full rooms (0 available beds) will be excluded!
     */
    public function getAvailableRooms($hostelId)
    {
        $rooms = Room::where('hostel_id', $hostelId)
            ->whereHas('beds', function ($q) {
                $q->where('status', 'available');
            })
            ->withCount([
                'beds as total_beds',
                'beds as available_beds' => function ($q) {
                    $q->where('status', 'available');
                }
            ])
            ->get();

        return response()->json($rooms);
    }

    /**
     * Get beds under a room that are AVAILABLE.
     * Occupied beds will be excluded!
     */
    public function getAvailableBeds($roomId)
    {
        $beds = Bed::where('room_id', $roomId)
            ->where('status', 'available')
            ->get();

        return response()->json($beds);
    }
}
