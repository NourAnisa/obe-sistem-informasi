{{-- Shared alert + action helpers --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-300 text-green-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-300 text-red-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
    ❌ {{ session('error') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-300 text-red-700 text-sm px-4 py-3 rounded-lg">
    <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif