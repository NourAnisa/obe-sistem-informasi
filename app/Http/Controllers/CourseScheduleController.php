<?php

namespace App\Http\Controllers;

use App\Models\{CourseSchedule, MataKuliah, Room};
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseScheduleController extends Controller
{
    public function __construct(private RoomAssignmentService $svc) {}

    /** GET /course-schedules */
    public function index(Request $request)
    {
        $ta  = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $day = $request->input('day');
        $sem = $request->input('semester');

        $schedules = CourseSchedule::with(['mataKuliah', 'room'])
            ->where('academic_year', $ta)
            ->when($day, fn($q) => $q->where('day_of_week', $day))
            ->when($sem, fn($q) => $q->where('semester', $sem))
            ->orderByRaw("FIELD(day_of_week,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('start_time')
            ->get();

        // Group by hari untuk tampilan jadwal
        $byDay = $schedules->groupBy('day_of_week');

        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $rooms       = Room::where('is_active', true)->orderBy('code')->get();
        $days        = RoomAssignmentService::DAYS;

        return view('course-schedules.index', compact('schedules', 'byDay', 'mataKuliahs', 'rooms', 'days', 'ta'));
    }

    /** POST /course-schedules */
    public function store(Request $request)
    {
        $data = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'class_name'     => 'nullable|string|max:10',
            'room_id'        => 'nullable|exists:rooms,id',
            'day_of_week'    => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'semester'       => 'required|integer|min:1|max:8',
            'academic_year'  => 'required|string|max:20',
        ]);

        // Cek konflik jika room dipilih
        if (!empty($data['room_id'])) {
            $conflict = $this->svc->checkConflict(
                $data['room_id'],
                $data['day_of_week'],
                $data['start_time'],
                $data['end_time']
            );
            if ($conflict) {
                return back()->withErrors(['room_id' => 'Ruangan sudah dipakai atau diblokir pada slot waktu ini.'])->withInput();
            }
        }

        CourseSchedule::create($data);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /** GET /course-schedules/{schedule}/edit */
    public function edit(CourseSchedule $courseSchedule)
    {
        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $rooms       = Room::where('is_active', true)->orderBy('code')->get();
        $days        = RoomAssignmentService::DAYS;

        return view('course-schedules.edit', compact('courseSchedule', 'mataKuliahs', 'rooms', 'days'));
    }

    /** PUT /course-schedules/{schedule} */
    public function update(Request $request, CourseSchedule $courseSchedule)
    {
        $data = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'class_name'     => 'nullable|string|max:10',
            'room_id'        => 'nullable|exists:rooms,id',
            'day_of_week'    => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'semester'       => 'required|integer|min:1|max:8',
            'academic_year'  => 'required|string|max:20',
        ]);

        if (!empty($data['room_id'])) {
            $conflict = $this->svc->hasScheduleConflict(
                $data['room_id'],
                $data['day_of_week'],
                $data['start_time'],
                $data['end_time'],
                $data['academic_year'],
                $courseSchedule->id // exclude self
            );
            if ($conflict) {
                return back()->withErrors(['room_id' => 'Ruangan sudah dipakai pada slot waktu ini.'])->withInput();
            }
        }

        $courseSchedule->update($data);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    /** DELETE /course-schedules/{schedule} */
    public function destroy(CourseSchedule $courseSchedule)
    {
        $courseSchedule->delete();
        return back()->with('success', 'Jadwal dihapus.');
    }

    /**
     * POST /api/rooms/auto-assign
     * Body: { mata_kuliah_id, day_of_week, start_time, end_time, academic_year, student_count? }
     */
    public function autoAssign(Request $request)
    {
        $data = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'day_of_week'    => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'academic_year'  => 'required|string|max:20',
            'class_name'     => 'nullable|string|max:10',
            'student_count'  => 'nullable|integer|min:0',
        ]);

        $studentCount = $data['student_count'] ?? DB::table('mahasiswa_mk')
            ->where('mata_kuliah_id', $data['mata_kuliah_id'])
            ->where('status', 'disetujui')
            ->count();

        $room = $this->svc->autoAssign(
            $data['mata_kuliah_id'],
            $studentCount,
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $data['academic_year']
        );

        if (!$room) {
            return response()->json([
                'status'  => 'no_room',
                'message' => "Tidak ada ruangan tersedia untuk {$studentCount} mahasiswa pada slot ini.",
            ], 422);
        }

        // Simpan jadwal dengan ruangan yang dipilih
        $schedule = CourseSchedule::updateOrCreate(
            [
                'mata_kuliah_id' => $data['mata_kuliah_id'],
                'class_name'     => $data['class_name'] ?? null,
                'day_of_week'    => $data['day_of_week'],
                'academic_year'  => $data['academic_year'],
            ],
            [
                'room_id'    => $room->id,
                'start_time' => $data['start_time'],
                'end_time'   => $data['end_time'],
                'semester'   => MataKuliah::find($data['mata_kuliah_id'])->semester,
            ]
        );

        return response()->json([
            'status'    => 'assigned',
            'room'      => [
                'id'        => $room->id,
                'code'      => $room->code,
                'name'      => $room->name,
                'full_name' => $room->full_name,
                'capacity'  => $room->capacity,
            ],
            'schedule_id'   => $schedule->id,
            'student_count' => $studentCount,
        ]);
    }
}
