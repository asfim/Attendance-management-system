<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\StudentEnrollment;
use App\Models\PromotionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PromotionService
{
    /**
     * Get students eligible for promotion.
     * We calculate an average GPA to determine Pass/Fail dynamically,
     * but ultimately return a list that the frontend can display.
     */
    private function calculateEligibilityInfo($student, $sessionId, $finalExam, $finalExamSchedules)
    {
        $totalMarks = \App\Models\MarksEntry::where('student_profile_id', $student->id)
            ->whereHas('examSchedule.examType', function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })
            ->sum('marks_obtained');

        $isEligible = false;

        if ($finalExam && $finalExamSchedules->count() > 0) {
            $passedAll = true;
            foreach ($finalExamSchedules as $schedule) {
                $markEntry = \App\Models\MarksEntry::where('exam_schedule_id', $schedule->id)
                    ->where('student_profile_id', $student->id)
                    ->first();

                if (!$markEntry || $markEntry->marks_obtained < $schedule->pass_marks) {
                    $passedAll = false;
                    break;
                }
            }
            $isEligible = $passedAll;
        }

        return [
            'total_marks' => $totalMarks,
            'is_eligible' => $isEligible
        ];
    }

    public function getEligibleStudents($sessionId, $classId, $sectionId, $toSessionId = null, $toClassId = null, $toSectionId = null, $fromShiftId = null)
    {
        // Find Final Exam for this session
        $finalExam = \App\Models\ExamType::where('session_id', $sessionId)
            ->where('name', 'like', '%final%')
            ->first();

        $finalExamSchedules = collect();
        if ($finalExam) {
            $finalExamSchedules = \App\Models\ExamSchedule::where('exam_type_id', $finalExam->id)
                ->where('class_id', $classId)
                ->get();
        }

        // Get students in this session, class, and section
        $students = StudentProfile::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('status', 'active')
            ->when($fromShiftId, function ($query, $shiftId) {
                return $query->where('shift_id', $shiftId);
            })
            ->get()
            ->map(function ($student) use ($sessionId, $finalExam, $finalExamSchedules) {

                $eligibility = $this->calculateEligibilityInfo($student, $sessionId, $finalExam, $finalExamSchedules);

                $student->calculated_status = $eligibility['is_eligible'] ? 'Eligible' : 'Not Eligible';
                $student->total_marks = $eligibility['total_marks'];
                $student->calculated_gpa = $eligibility['total_marks']; // Or actual GPA if calculated

                return $student;
            });

        // Sort by eligibility (Eligible first) and then total marks descending
        $students = $students->sortByDesc(function ($student) {
            return [
                $student->calculated_status === 'Eligible' ? 1 : 0,
                $student->total_marks
            ];
        })->values();

        if ($toSessionId && $toClassId && $toSectionId) {
            $assignedRolls = [];
            foreach ($students as $student) {
                if ($student->calculated_status === 'Eligible') {
                    $roll = $this->calculateNextAvailableRoll($toSessionId, $toClassId, $toSectionId, $assignedRolls);
                    $student->projected_roll = $roll;
                    $assignedRolls[] = $roll;
                } else {
                    $student->projected_roll = null;
                }
            }
        }

        return $students;
    }

    /**
     * Find the next available roll number in a specific class/section/session
     */
    public function calculateNextAvailableRoll($sessionId, $classId, $sectionId, $existingRolls = [])
    {
        // Existing rolls in the target class
        $takenRolls = StudentProfile::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('roll_no')
            ->map(fn($r) => (int)$r)
            ->toArray();

        // Combine with rolls already assigned during this promotion batch
        $allTaken = array_merge($takenRolls, $existingRolls);

        $roll = 1;
        while (in_array($roll, $allTaken)) {
            $roll++;
        }

        return $roll;
    }

    /**
     * Promote a batch of students to a new session, class, and section.
     */
    public function promoteStudents($studentIds, $fromSession, $fromClass, $fromSection, $toSession, $toClass, $toSection, $toShift = null)
    {
        $studentsWithMarks = StudentProfile::with('user')->whereIn('id', $studentIds)->get();

        // Find Final Exam for this session
        $finalExam = \App\Models\ExamType::where('session_id', $fromSession)
            ->where('name', 'like', '%final%')
            ->first();

        $finalExamSchedules = collect();
        if ($finalExam) {
            $finalExamSchedules = \App\Models\ExamSchedule::where('exam_type_id', $finalExam->id)
                ->where('class_id', $fromClass)
                ->get();
        }

        // Merit-based sorting: Calculate total marks for the outgoing session
        $studentsWithMarks = $studentsWithMarks->map(function ($student) use ($fromSession, $finalExam, $finalExamSchedules) {
            $eligibility = $this->calculateEligibilityInfo($student, $fromSession, $finalExam, $finalExamSchedules);

            $student->total_marks = $eligibility['total_marks'];
            $student->is_eligible = $eligibility['is_eligible'];
            return $student;
        });

        // Sort students descending by eligibility first, then by total marks
        // This pushes all Not Eligible students to the bottom.
        // Eligible students get the top ranks, and therefore the lowest available rolls.
        $sortedStudents = $studentsWithMarks->sortByDesc(function ($student) {
            return [
                $student->is_eligible ? 1 : 0,
                $student->total_marks
            ];
        })->values();

        $promotedCount = 0;

        DB::transaction(function () use ($sortedStudents, $fromSession, $fromClass, $fromSection, $toSession, $toClass, $toSection, $toShift, &$promotedCount) {

            $assignedRolls = [];

            foreach ($sortedStudents as $studentData) {
                $student = StudentProfile::lockForUpdate()->find($studentData->id);

                if (!$student) continue;

                // Ensure the student is actually in the from class/session to prevent anomalies
                if ($student->session_id != $fromSession || $student->class_id != $fromClass) {
                    continue; // Skip, they might have already been promoted or moved manually
                }

                // Check for duplicate enrollment in the target year
                $duplicate = StudentEnrollment::where('student_profile_id', $student->id)
                    ->where('session_id', $toSession)
                    ->where('class_id', $toClass)
                    ->exists();

                if ($duplicate) {
                    throw new \Exception("Duplicate enrollment detected for student: " . $student->user->name);
                }

                // Get next available roll or assign manual placeholder
                if ($studentData->is_eligible) {
                    $newRoll = $this->calculateNextAvailableRoll($toSession, $toClass, $toSection, $assignedRolls);
                    $assignedRolls[] = $newRoll; // Keep track of assigned rolls in this batch
                } else {
                    $newRoll = 'TBA-' . $student->id;
                }

                // 1. Create Old Enrollment Record (to preserve history)
                // We assume if it doesn't exist yet, we create it to snapshot their past state.
                $oldEnrollment = StudentEnrollment::create([
                    'student_profile_id' => $student->id,
                    'session_id' => $student->session_id,
                    'class_id' => $student->class_id,
                    'section_id' => $student->section_id,
                    'roll_no' => $student->roll_no,
                    'status' => 'completed',
                    'promotion_status' => 'promoted'
                ]);

                // 2. Create New Enrollment Record
                $newEnrollment = StudentEnrollment::create([
                    'student_profile_id' => $student->id,
                    'session_id' => $toSession,
                    'class_id' => $toClass,
                    'section_id' => $toSection,
                    'roll_no' => $newRoll,
                    'status' => 'active',
                    'previous_enrollment_id' => $oldEnrollment->id
                ]);

                // 3. Log the promotion
                PromotionLog::create([
                    'student_profile_id' => $student->id,
                    'old_session_id' => $student->session_id,
                    'old_class_id' => $student->class_id,
                    'old_section_id' => $student->section_id,
                    'old_roll_no' => $student->roll_no,
                    'new_session_id' => $toSession,
                    'new_class_id' => $toClass,
                    'new_section_id' => $toSection,
                    'new_roll_no' => $newRoll,
                    'promoted_by' => Auth::id(),
                    'status' => 'promoted',
                    'reason' => 'Annual Promotion'
                ]);

                // 4. Update the Student Profile with the new current state (Hybrid Approach)
                $student->update([
                    'session_id' => $toSession,
                    'class_id' => $toClass,
                    'section_id' => $toSection,
                    'roll_no' => $newRoll,
                    'shift_id' => $toShift ?? $student->shift_id,
                ]);

                // 5. Generate Fees (Future Enhancement based on Fee Structures)
                // $this->generateFeesForNewClass($student, $toSession, $toClass);

                $promotedCount++;
            }
        });

        return $promotedCount;
    }

    /**
     * Rollback a specific promotion.
     */
    public function rollbackPromotion(PromotionLog $log)
    {
        DB::transaction(function () use ($log) {
            $student = $log->student;

            // Delete the new enrollment
            StudentEnrollment::where('student_profile_id', $student->id)
                ->where('session_id', $log->new_session_id)
                ->where('class_id', $log->new_class_id)
                ->delete();

            // Find and restore the old enrollment to active
            $oldEnrollment = StudentEnrollment::where('student_profile_id', $student->id)
                ->where('session_id', $log->old_session_id)
                ->where('class_id', $log->old_class_id)
                ->first();

            if ($oldEnrollment) {
                $oldEnrollment->update(['status' => 'active', 'promotion_status' => null]);
            }

            // Revert student profile back to old state
            $student->update([
                'session_id' => $log->old_session_id,
                'class_id' => $log->old_class_id,
                'section_id' => $log->old_section_id,
                'roll_no' => $log->old_roll_no,
            ]);

            // Update log
            $log->update(['status' => 'rolled_back', 'reason' => 'Rollback requested by ' . Auth::user()->name]);
        });
    }
}
