<x-dashboardheader-layout>
    <div class="min-h-screen py-10 px-4 sm:px-6">
        {{-- Breadcrumbs --}}
        <div class="max-w-7xl mx-auto text-black text-[10px] py-4 px-2 uppercase font-black tracking-[0.2em] mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-red-700 transition-colors">Dashboard</a> 
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-red-700">My Appointments</span>
        </div>

        <div class="max-w-7xl mx-auto">
            {{-- Notifications Section --}}
            @if(isset($notifications) && count($notifications) > 0)
                <div class="mb-8 space-y-3">
                    <div class="flex items-center justify-between px-4">
                        <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">
                            🔔 Updates ({{ count($notifications) }})
                        </h3>
                        <button onclick="markAllNotificationsAsRead()" type="button" class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-red-700 transition">
                            Mark all as read
                        </button>
                    </div>
                    
                    @foreach($notifications as $notification)
                        <div class="relative group rounded-[2rem] p-6 flex items-center gap-6 shadow-sm border-2 transition-all
                            {{ $notification['type'] === 'success' ? 'bg-green-50 border-green-100' : '' }}
                            {{ $notification['type'] === 'error' ? 'bg-red-50 border-red-100' : '' }}
                            {{ $notification['type'] === 'info' ? 'bg-blue-50 border-blue-100' : '' }}
                        " id="notification-{{ $notification['id'] }}">
                            <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center text-2xl bg-white shadow-sm">
                                {!! $notification['type'] === 'success' ? '✅' : ($notification['type'] === 'error' ? '❌' : 'ℹ️') !!}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black uppercase tracking-tighter text-lg leading-none mb-1
                                    {{ $notification['type'] === 'success' ? 'text-green-800' : '' }}
                                    {{ $notification['type'] === 'error' ? 'text-red-800' : '' }}
                                    {{ $notification['type'] === 'info' ? 'text-blue-800' : '' }}
                                ">{{ $notification['title'] }}</h4>
                                <p class="text-xs font-bold uppercase tracking-tight opacity-80">{{ $notification['message'] }}</p>
                                
                                @if(isset($notification['qr_link']))
                                    <a href="{{ $notification['qr_link'] }}" class="inline-block mt-3 bg-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:scale-105 transition-transform">
                                        View Digital Pass →
                                    </a>
                                @endif
                            </div>
                            <button onclick="dismissNotification('{{ $notification['key'] }}', '{{ $notification['id'] }}')" class="absolute top-4 right-6 text-gray-400 hover:text-black font-black">✕</button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Main Container --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
                
                {{-- Toolbar Section --}}
                <div class="p-8 md:p-10 border-b border-gray-50 flex flex-col lg:flex-row justify-between items-center gap-6">
                    <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter border-l-8 border-red-700 pl-4">
                        Scheduled Appointments
                    </h3>
                    
                    <div class="flex flex-col sm:flex-row items-center w-full lg:w-auto gap-4">
                        <div class="relative w-full sm:w-80 group">
                            <input type="text" id="apptSearch" placeholder="Search Pet or Service..." 
                                class="w-full pl-5 pr-12 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:ring-0 focus:border-red-700 focus:bg-white text-xs font-bold uppercase tracking-widest transition-all">
                        </div>

                        <a href="{{ route('appointments.create') }}" 
                            class="w-full sm:w-auto bg-red-700 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-red-800 transition-all shadow-lg active:scale-95 text-center">
                            + Book Appointment
                        </a>
                    </div>
                </div>

                <div class="p-6 md:p-10">
                    @if($appointments->isEmpty())
                        <div class="py-24 text-center bg-gray-50/50 rounded-[2rem] border-4 border-dashed border-gray-100">
                            <div class="text-7xl mb-6 opacity-20">📅</div>
                            <h3 class="text-xl font-black text-gray-400 uppercase tracking-widest">No Appointments Found</h3>
                            <p class="text-gray-400 mb-8 font-bold text-xs uppercase tracking-widest">You don't have any scheduled visits yet.</p>
                        </div>
                    @else
                        <div id="apptGrid" class="space-y-6">
                            @foreach($appointments as $appointment)
                                <div class="appt-card group bg-white rounded-[2rem] border-2 border-gray-100 p-6 flex flex-col lg:flex-row items-center gap-8 hover:border-red-600 transition-all duration-300">
                                    
                                    {{-- Pet Identity & Service --}}
                                    <div class="flex items-center w-full lg:w-1/4 space-x-5">
                                        <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-gray-50 border-2 border-gray-100 flex items-center justify-center text-3xl group-hover:bg-red-50 transition-colors">
                                            🐾
                                        </div>
                                        <div class="truncate">
                                            <h4 class="appt-pet text-xl font-black text-gray-900 uppercase tracking-tighter leading-none group-hover:text-red-700 transition-colors">
                                                {{ $appointment->pet->Pet_Name }}
                                            </h4>
                                            <p class="appt-service text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">
                                                {{ $appointment->service->Service_Name }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Time & Date Grid --}}
                                    <div class="flex-1 grid grid-cols-2 md:grid-cols-3 gap-6 w-full lg:px-10 border-y lg:border-y-0 lg:border-x border-gray-50 py-6 lg:py-0">
                                        <div>
                                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Date</span>
                                            <span class="font-bold text-gray-800 uppercase text-xs">{{ $appointment->Date->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Time</span>
                                            <span class="font-bold text-gray-800 uppercase text-xs">{{ date('h:i A', strtotime($appointment->Time)) }}</span>
                                        </div>
                                        <div class="col-span-2 md:col-span-1">
                                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Status</span>
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter
                                                {{ $appointment->Status == 'Pending' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                                {{ in_array($appointment->Status, ['Approved', 'Confirmed']) ? 'bg-green-50 text-green-700' : '' }}
                                                {{ $appointment->Status == 'Cancelled' ? 'bg-red-50 text-red-700' : '' }}
                                                {{ $appointment->Status == 'Completed' ? 'bg-blue-50 text-blue-700' : '' }}">
                                                {{ $appointment->Status }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center justify-end w-full lg:w-auto gap-3">
                                        @if($appointment->Status == 'Approved' || $appointment->Status == 'Confirmed')
                                            <a href="{{ route('appointments.qrcode', $appointment->Appointment_ID) }}" 
                                                class="flex-1 lg:flex-none text-center bg-black text-white px-6 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-800 transition-all shadow-lg active:scale-95">
                                                Digital Pass
                                            </a>
                                        @endif
                                        
                                        @if($appointment->Status == 'Pending')
                                            <form method="POST" action="{{ route('appointments.cancel', $appointment->Appointment_ID) }}" onsubmit="return confirm('CANCEL THIS APPOINTMENT?');" class="flex-1 lg:flex-none">
                                                @csrf
                                                <button type="submit" class="w-full bg-white border-2 border-red-700 text-red-700 px-6 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-700 hover:text-white transition-all">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time Search Functionality
        document.getElementById('apptSearch').addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let cards = document.querySelectorAll('.appt-card');
            cards.forEach(card => {
                let pet = card.querySelector('.appt-pet').textContent.toUpperCase();
                let service = card.querySelector('.appt-service').textContent.toUpperCase();
                card.style.display = (pet.includes(filter) || service.includes(filter)) ? "" : "none";
            });
        });

        function dismissNotification(key, id) {
            document.getElementById('notification-' + id).style.opacity = '0';
            setTimeout(() => document.getElementById('notification-' + id).remove(), 300);
            fetch('{{ route("appointments.notifications.markSeen") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ key: key })
            });
        }

        function markAllNotificationsAsRead() {
            fetch('{{ route("notifications.markAllSeen") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Simply reload the page to show updated state
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Reload anyway as fallback
                window.location.reload();
            });
        }
    </script>
</x-dashboardheader-layout>