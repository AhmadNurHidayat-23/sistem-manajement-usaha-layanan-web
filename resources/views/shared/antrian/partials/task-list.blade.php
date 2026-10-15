<div class="bg-white rounded-xl border border-[#E4E7EC] shadow-2xs overflow-hidden">
    @if($tasks->isEmpty())
        <div class="py-12 px-4 text-center">
            <div class="w-12 h-12 rounded-full bg-[#F5F8FC] text-[#667085] mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-[#101828] m-0">
                {{ $isHistory ? 'Belum ada riwayat tugas sebelumnya.' : 'Belum ada tugas yang ditugaskan kepada Anda hari ini.' }}
            </h3>
            <p class="text-xs text-[#667085] mt-1 m-0">
                {{ $isHistory ? 'Semua tugas layanan yang telah selesai akan tercatat di sini.' : 'Tugas layanan janji temu dan drop-off yang ditugaskan kepada Anda akan tampil di sini.' }}
            </p>
        </div>
    @else
        <div class="divide-y divide-[#E4E7EC]">
            @foreach($tasks as $task)
                @php
                    $isAppointment = $task['task_type'] === 'Appointment';
                    $statusClass = match($task['status_ui']) {
                        'Menunggu' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'Dikerjakan' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Menunggu Konfirmasi' => 'bg-slate-100 text-slate-700 border-slate-200',
                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                    };
                @endphp
                <div class="p-4 sm:px-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-[#F5F8FC]/50 transition-colors">
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        {{-- Jam Pelaksanaan --}}
                        <div class="w-14 h-12 rounded-lg bg-[#DCE8FF] flex flex-col items-center justify-center shrink-0 font-bold text-[#062072] text-xs leading-tight">
                            <span>{{ $task['time'] }}</span>
                            <span class="text-[9px] font-normal text-[#0B3A9E]">WIB</span>
                        </div>

                        {{-- Informasi Layanan & Pelanggan --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-bold text-sm text-[#101828] truncate">{{ $task['service_name'] }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium {{ $isAppointment ? 'bg-[#EEF4FF] text-[#0B3A9E]' : 'bg-[#F4F3FF] text-[#5925DC]' }}">
                                    @if($isAppointment)
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Appointment</span>
                                    @else
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <span>Drop-off</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[#667085] flex-wrap">
                                <span class="text-[#344054] font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-[#98A2B3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $task['customer_name'] }}
                                </span>
                                <span>&bull;</span>
                                <span>{{ $task['branch_name'] }}</span>
                                @if(isset($task['date']))
                                    <span>&bull;</span>
                                    <span>{{ $task['date'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Status Badge & Aksi --}}
                    <div class="flex items-center justify-between md:justify-end gap-2.5 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-[#F2F4F7]">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                            {{ $task['status_ui'] }}
                        </span>

                        <div class="flex items-center gap-2">
                            @if(! $isHistory)
                                @if(!empty($task['can_start']))
                                    <form method="POST" action="{{ $task['action_url'] }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $task['next_status'] }}">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#062072] text-white hover:bg-[#04184F] transition-colors cursor-pointer shadow-2xs">
                                            Mulai Pengerjaan
                                        </button>
                                    </form>
                                @elseif(!empty($task['can_complete']))
                                    <form method="POST" action="{{ $task['action_url'] }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $task['next_status'] }}">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors cursor-pointer shadow-2xs">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <a href="{{ $task['detail_url'] }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-[#D0D5DD] text-[#344054] hover:bg-[#F5F8FC] hover:text-[#062072] transition-colors shadow-2xs">
                                Detail Tugas
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
