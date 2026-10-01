<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBlockRule;
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(private RoomAssignmentService $svc) {}

    /** GET /rooms */
    public function index(Request $request)
    {
        $rooms = Room::withCount('courseSchedules')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->building, fn($q) => $q->where('building', $request->building))
            ->when($request->active !== null, fn($q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('building')->orderBy('floor')->orderBy('code')
            ->get();

        $buildings = Room::distinct()->orderBy('building')->pluck('building')->filter();
        $ta        = config('obe.tahun_akademik', '2025/2026');

        return view('rooms.index', compact('rooms', 'buildings', 'ta'));
    }

    /** GET /rooms/create */
    public function create()
    {
        return view('rooms.create');
    }

    /** POST /rooms */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|max:20|unique:rooms,code',
            'name'        => 'required|string|max:255',
            'building'    => 'nullable|string|max:100',
            'floor'       => 'nullable|string|max:10',
            'capacity'    => 'required|integer|min:1|max:500',
            'type'        => 'required|in:class,lab,aula,online,field',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        Room::create($data);

        return back()->with('success', "Ruangan {$data['code']} berhasil ditambahkan.");
    }

    /** GET /rooms/{room}/edit */
    public function edit(Room $room)
    {
        $room->load('blockRules');
        return view('rooms.edit', compact('room'));
    }

    /** PUT /rooms/{room} */
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'code'        => "required|string|max:20|unique:rooms,code,{$room->id}",
            'name'        => 'required|string|max:255',
            'building'    => 'nullable|string|max:100',
            'floor'       => 'nullable|string|max:10',
            'capacity'    => 'required|integer|min:1|max:500',
            'type'        => 'required|in:class,lab,aula,online,field',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $room->update($data);

        return back()->with('success', "Ruangan {$room->code} berhasil diperbarui.");
    }

    /** DELETE /rooms/{room} */
    public function destroy(Room $room)
    {
        if ($room->courseSchedules()->exists()) {
            return back()->with('error', "Tidak bisa menghapus ruangan yang masih memiliki jadwal.");
        }
        $room->delete();
        return back()->with('success', "Ruangan {$room->code} dihapus.");
    }

    // ── Block Rules ─────────────────────────────────────────────────

    /** POST /rooms/{room}/block-rules */
    public function storeBlockRule(Request $request, Room $room)
    {
        $data = $request->validate([
            'day_of_week' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string|max:255',
        ]);

        $room->blockRules()->create($data);

        return back()->with('success', 'Aturan blok berhasil ditambahkan.');
    }

    /** DELETE /rooms/{room}/block-rules/{rule} */
    public function destroyBlockRule(Room $room, RoomBlockRule $rule)
    {
        abort_if($rule->room_id !== $room->id, 404);
        $rule->delete();
        return back()->with('success', 'Aturan blok dihapus.');
    }

    // ── API Endpoints ────────────────────────────────────────────────

    /** GET /api/rooms/available?capacity=&day=&start=&end=&ta= */
    public function available(Request $request)
    {
        $request->validate([
            'capacity' => 'required|integer|min:1',
            'day'      => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start'    => 'required|date_format:H:i',
            'end'      => 'required|date_format:H:i|after:start',
        ]);

        $rooms = $this->svc->findAvailableRooms(
            $request->integer('capacity'),
            $request->day,
            $request->start,
            $request->end,
            $request->input('ta', config('obe.tahun_akademik', '2025/2026'))
        );

        return response()->json($rooms->map(fn($r) => [
            'id'        => $r->id,
            'code'      => $r->code,
            'name'      => $r->name,
            'full_name' => $r->full_name,
            'capacity'  => $r->capacity,
            'type'      => $r->type,
            'type_label' => $r->type_label,
            'building'  => $r->building,
            'floor'     => $r->floor,
        ]));
    }

    /** GET /api/rooms/utilization?ta= */
    public function utilization(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $data = $this->svc->getAllRoomsUtilization($ta);

        return response()->json($data->map(fn($row) => [
            'room_id'          => $row['room_id'],
            'code'             => $row['room']->code,
            'name'             => $row['room']->name,
            'building'         => $row['room']->building,
            'capacity'         => $row['room']->capacity,
            'type'             => $row['room']->type,
            'scheduled_hours'  => $row['scheduled_hours'],
            'operational_hours' => $row['operational_hours'],
            'utilization_pct'  => $row['utilization_pct'],
            'schedule_count'   => $row['schedule_count'],
        ]));
    }
}
