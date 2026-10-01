{{--
    Reusable Program Switcher Component for Public Pages
    Usage: @include('components.program-switcher', ['currentRoute' => 'cpl.index'])
--}}
@if(isset($allPrograms) && $allPrograms->count() > 1)
<div class="bg-white border-b border-slate-100 shadow-sm" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center gap-3">
        <span class="text-xs text-slate-400 font-medium flex-shrink-0">Program Studi:</span>

        <div class="relative" @click.away="open = false">
            <button @click="open = !open"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-brand-700 bg-brand-50 border border-brand-200 hover:bg-brand-100 transition">
                <span>📚</span>
                <span>{{ $program?->jenjang }} {{ $program?->nama ?? config('obe.prodi') }}</span>
                <svg class="w-3.5 h-3.5 text-brand-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                class="absolute left-0 top-full mt-1.5 w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 overflow-hidden">
                @foreach($allPrograms->groupBy(fn($p) => $p->faculty?->nama ?? 'Lainnya') as $fak => $prodis)
                    <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $fak }}</span>
                    </div>
                    @foreach($prodis as $p)
                    <a href="{{ route($currentRoute ?? 'home') }}?program_id={{ $p->id }}"
                        class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition
                            {{ ($program && $program->id === $p->id) ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}">
                        @if($program && $program->id === $p->id)
                            <span class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-200 flex-shrink-0"></span>
                        @endif
                        <span>{{ $p->jenjang }} {{ $p->nama }}</span>
                        @if($p->kode_prodi)
                            <span class="ml-auto text-[10px] font-mono bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">{{ $p->kode_prodi }}</span>
                        @endif
                    </a>
                    @endforeach
                @endforeach
            </div>
        </div>

        @if($program)
        <span class="text-xs text-slate-400">
            · {{ $program->faculty?->nama ?? '' }}
            @if($program->akreditasi) · Akreditasi: <strong class="text-slate-600">{{ $program->akreditasi }}</strong> @endif
        </span>
        @endif
    </div>
</div>
@endif
