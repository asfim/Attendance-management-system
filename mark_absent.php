<?php
$date = now()->format('Y-m-d');
$students = \App\Models\StudentProfile::all();
foreach($students as $s) {
    \App\Models\Attendance::firstOrCreate(
        ['attendance_date' => $date, 'attendable_type' => \App\Models\StudentProfile::class, 'attendable_id' => $s->id],
        ['status' => 'absent', 'remarks' => 'Auto-marked absent initially']
    );
}

$staff = \App\Models\StaffProfile::all();
foreach($staff as $s) {
    \App\Models\Attendance::firstOrCreate(
        ['attendance_date' => $date, 'attendable_type' => \App\Models\StaffProfile::class, 'attendable_id' => $s->id],
        ['status' => 'absent', 'remarks' => 'Auto-marked absent initially']
    );
}
echo "Done!\n";
