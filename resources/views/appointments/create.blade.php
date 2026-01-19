<x-dashboardheader-layout>
    <head>
        @vite([
            'resources/css/app.css',
            'resources/css/create_appointment.css',
            'resources/js/app.js'
        ])
    </head>
    <body class="min-h-screen">
            <div class="max-w-5xl mx-auto text-gray-400 text-xs py-4 px-6 uppercase font-black tracking-widest">
            <a href="{{ route('dashboard') }}" class="hover:text-red-700 transition-colors">Dashboard</a> 
            <span class="mx-2">/</span>
            <span class="font-black uppercase tracking-widest text-red-700">Confirm Appointment</span>
        </div>

        <div class="max-w-5xl mx-auto px-4 pb-12">
            {{-- Main Flattened Landscape Card --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
                
                <div class="p-8 md:p-12">
                    {{-- Centered Header & Progress --}}
                    <div class="flex flex-col items-center justify-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-black text-gray-900 uppercase tracking-tight text-center">Book Appointment</h2>
                        <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">Step 1: Appointment Details</p>

                        {{-- Progress Bar --}}
                        <div class="flex items-center justify-center w-full max-w-xs relative">
                            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-200 -translate-y-1/2 z-0"></div>
                            <div class="flex justify-between w-full relative z-10">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-red-700 text-white text-xs font-black shadow-lg border-4 border-white">1</div>
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-white text-xs font-black border-4 border-white">2</div>
                            </div>
                        </div>
                    </div>

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="mb-10 bg-red-50 border-l-4 border-red-600 p-5 rounded-r-xl">
                            <h3 class="text-red-800 font-black uppercase text-[10px] tracking-widest mb-2">Check the following:</h3>
                            <ul class="text-xs text-red-700 list-disc list-inside space-y-1 font-medium">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('appointments.preview') }}" id="appointmentForm" class="space-y-12" novalidate>
                        @csrf

                        {{-- Section 1: Service Selection --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6">
                                <span class="text-red-700 font-black uppercase text-xs tracking-widest">01. Select Service</span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($services as $service)
                                    <label class="relative group cursor-pointer h-full">
                                        <input type="radio" name="Service_ID" value="{{ $service->Service_ID }}" class="peer hidden" {{ old('Service_ID') == $service->Service_ID ? 'checked' : '' }} required>
                                        <div class="h-full min-h-[80px] flex flex-col justify-center border-2 border-gray-100 rounded-2xl p-5 transition-all 
                                                    hover:border-red-600 hover:bg-red-50/50
                                                    peer-checked:border-red-600 peer-checked:bg-red-50/50">
                                            
                                            <span class="block text-center font-black text-gray-700 uppercase text-[11px] tracking-wider group-hover:text-red-700 transition-colors">
                                                {{ $service->Service_Name }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        {{-- Section 2: Date & Time --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6">
                                <span class="text-red-700 font-black uppercase text-xs tracking-widest">02. Schedule Slot</span>
                            </div>

                            <div class="flex flex-col lg:flex-row gap-8">
                                {{-- Calendar --}}
                                <div class="flex-1 bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 id="monthYear" class="font-black text-gray-900 uppercase tracking-widest text-[11px]"></h3>
                                        <div class="flex gap-2">
                                            <button type="button" id="prevMonth" class="p-2 bg-white border border-red-600 rounded-lg text-black hover:text-red-700 transition-colors text-xs font-black">PREV</button>
                                            <button type="button" id="nextMonth" class="p-2 bg-white border border-red-600 rounded-lg text-black hover:text-red-700 transition-colors text-xs font-black">NEXT</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1 text-center mb-4">
                                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">{{ $day }}</span>
                                        @endforeach
                                    </div>
                                    <div id="calendarDays" class="grid grid-cols-7 gap-2"></div>
                                    <input type="hidden" name="Date" id="selectedDate" value="{{ old('Date') }}">
                                </div>

                                {{-- Time Selection --}}
                                <div class="w-full lg:w-72">
                                    <x-input-label value="Available Slots" class="text-red-700 font-bold uppercase text-[10px] tracking-widest mb-2" />
                                    <div class="relative">
                                        <button type="button" id="timeToggle" 
                                            class="group w-full flex justify-between items-center bg-gray-50/50 border-2 border-gray-100 rounded-xl px-5 py-3.5 text-left transition-all 
                                                hover:border-red-600 hover:bg-red-50/50 focus:border-red-600 outline-none">
                                            
                                            <span id="selectedTimeText" class="text-gray-500 font-bold text-xs uppercase tracking-widest group-hover:text-red-700 transition-colors">
                                                Select a time
                                            </span>
                                            
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-red-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>

                                        <div id="timeMenu" class="hidden absolute left-0 w-full mt-2 bg-white border border-gray-100 shadow-2xl rounded-2xl z-50 max-h-60 overflow-y-auto p-2">
                                            {{-- Populated via JS --}}
                                        </div>
                                    </div>
                                    <input type="hidden" name="Time" id="timeInput" value="{{ old('Time') }}">
                                </div>
                            </div>
                        </section>

                        {{-- Section 3: Pet Selection --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6">
                                <span class="text-red-700 font-black uppercase text-xs tracking-widest">03. Choose Patient</span>
                            </div>

                            @if($pets->isEmpty())
                                <div class="text-center py-12 bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-200">
                                    <p class="text-gray-400 text-xs font-black uppercase tracking-widest mb-4">No pets found</p>
                                    <a href="{{ route('pets.create') }}" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-800 transition-all shadow-lg">
                                        Register Pet
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                    @foreach($pets as $pet)
                                        <label class="cursor-pointer group h-full">
                                            <input type="radio" name="Pet_ID" value="{{ $pet->Pet_ID }}" class="peer hidden" {{ old('Pet_ID') == $pet->Pet_ID ? 'checked' : '' }} required>
                                            
                                            <div class="h-full min-h-[110px] p-4 bg-white border-2 border-gray-100 rounded-2xl text-center transition-all flex flex-col items-center justify-center
                                                        group-hover:border-red-600 group-hover:bg-red-50/50
                                                        peer-checked:border-red-600 peer-checked:bg-red-50/50">
                                                
                                                {{-- Paw Icon Integration --}}
                                                <div class="w-10 h-10 mb-3 bg-gray-50 rounded-xl flex items-center justify-center text-lg transition-colors group-hover:bg-white peer-checked:bg-white">
                                                    🐾
                                                </div>

                                                <div class="font-black text-gray-700 text-[10px] uppercase truncate tracking-widest transition-colors group-hover:text-red-700 peer-checked:text-red-700">
                                                    {{ $pet->Pet_Name }}
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach

                                    <a href="{{ route('pets.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-red-600 rounded-2xl transition-all text-red-600 hover:text-red-700 hover:border-red-600 hover:bg-red-50/50 min-h-[110px]">
                                        <span class="text-xl mb-1">＋</span>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Add New</span>
                                    </a>
                                </div>
                            @endif
                        </section>

                        {{-- Action Footer --}}
                        <div class="pt-10 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 gap-6">
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest text-center md:text-left">
                                Need help? <a href="#" class="text-red-700 hover:underline">Contact Clinic</a>
                            </p>

                            <button type="submit" id="submitBtn"
                                class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm disabled:opacity-50"
                                {{ $pets->isEmpty() ? 'disabled' : '' }}>
                                Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        // =====================================================
        // STATE & CONFIGURATION
        // =====================================================
        let clinicSchedule = { default_closed_days: [0, 6], opened_dates: [], closed_dates: [] };
        let masterTimeSlots = []; 
        let currentTakenTimes = []; // NEW: Track taken times globally
        let currentDate = new Date(); 
        const todayDate = new Date(); 
        let selectedDate = document.getElementById('selectedDate').value || null;

        // =====================================================
        // CORE INITIALIZATION
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            initServiceSelection();
            initPetSelection();
            initTimeDropdown();
            initCalendarNavigation();
            
            Promise.all([
                fetchClinicSchedule(),
                fetchMasterTimeSlots()
            ]).then(() => {
                renderCalendar();
                if (selectedDate) fetchTakenTimes(selectedDate);
            });
        });

        // =====================================================
        // FETCHING DATA
        // =====================================================
        async function fetchMasterTimeSlots() {
            try {
                const response = await fetch('/appointments/time-slots');
                if (response.ok) {
                    masterTimeSlots = await response.json();
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
            }
        }

        async function fetchTakenTimes(date) {
            if (!date) return;
            try {
                const res = await fetch(`/appointments/taken-times?date=${date}`);
                const data = await res.json();
                currentTakenTimes = data.takenTimes || []; // Store globally
                renderTimeSlots(currentTakenTimes);
            } catch (err) {
                console.error('Fetch Times Error:', err);
            }
        }

        // =====================================================
        // TIME DROPDOWN RENDERING
        // =====================================================
        function renderTimeSlots(takenTimes = currentTakenTimes) {
            const timeMenu = document.getElementById('timeMenu');
            const timeInput = document.getElementById('timeInput');
            const selectedTimeText = document.getElementById('selectedTimeText');
            timeMenu.innerHTML = '';

            // 1. Check if date is selected
            if (!selectedDate) {
                timeMenu.innerHTML = '<div class="px-4 py-8 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Select a date on the<br>calendar first</div>';
                return;
            }

            // 2. Render Slots
            masterTimeSlots.forEach(slot => {
                const isTaken = takenTimes.includes(slot.Slot_Val);
                const div = document.createElement('div');
                
                // Fixed styling logic
                div.className = `px-5 py-3.5 text-[11px] font-black tracking-widest transition-colors border-b border-gray-50 last:border-0 uppercase 
                    ${isTaken 
                        ? 'bg-gray-100 text-gray-300 cursor-not-allowed' 
                        : 'hover:bg-red-50 text-gray-700 hover:text-red-700 cursor-pointer'}`;
                
                div.textContent = isTaken ? `${slot.Slot_Display} (FULL)` : slot.Slot_Display;

                // 3. Strict Click Logic: Only allow if NOT taken
                div.onclick = (e) => {
                    if (isTaken) {
                        e.stopPropagation();
                        return; // Do absolutely nothing if full
                    }
                    
                    timeInput.value = slot.Slot_Val;
                    selectedTimeText.textContent = slot.Slot_Display;
                    selectedTimeText.className = "text-gray-900 font-black tracking-widest uppercase";
                    timeMenu.classList.add('hidden');
                };
                
                timeMenu.appendChild(div);
            });
        }

        // =====================================================
        // CALENDAR RENDERING
        // =====================================================
        function renderCalendarDays() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const todayReset = new Date().setHours(0,0,0,0);

            let daysHTML = '';
            
            for (let i = 0; i < firstDay; i++) {
                daysHTML += `<div class="aspect-square border-2 border-transparent"></div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isPast = new Date(dateStr + 'T00:00:00').getTime() < todayReset;
                const isClosed = isDateClosed(dateStr);
                const isSelected = selectedDate === dateStr;
                
                let stateClass = "text-gray-700 hover:border-red-700 hover:text-red-700 cursor-pointer border-gray-100 hover:bg-gray-50";
                
                if (isPast || isClosed) {
                    stateClass = "text-gray-200 cursor-not-allowed opacity-40 border-transparent bg-gray-50/30";
                }
                
                if (isSelected) {
                    stateClass = "bg-red-700 text-white font-black border-red-700 shadow-md transform scale-105 z-10";
                }

                daysHTML += `
                    <div class="calendar-day aspect-square flex items-center justify-center rounded-xl border-2 transition-all text-[11px] font-black ${stateClass}" 
                        onclick="${(isPast || isClosed) ? '' : `selectDate('${dateStr}')`}">
                        ${day}
                    </div>`;
            }
            document.getElementById('calendarDays').innerHTML = daysHTML;
        }

        // =====================================================
        // UTILITIES & NAVIGATION
        // =====================================================
        function initCalendarNavigation() {
            const prevBtn = document.getElementById('prevMonth');
            const nextBtn = document.getElementById('nextMonth');
            prevBtn.addEventListener('click', () => {
                if (currentDate.getMonth() > todayDate.getMonth() || currentDate.getFullYear() > todayDate.getFullYear()) {
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    renderCalendar();
                }
            });
            nextBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });
        }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            document.getElementById('monthYear').textContent = new Date(year, month)
                .toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase();
            
            const prevBtn = document.getElementById('prevMonth');
            const isCurrentMonth = year === todayDate.getFullYear() && month === todayDate.getMonth();
            prevBtn.disabled = isCurrentMonth;
            prevBtn.classList.toggle('opacity-20', isCurrentMonth);
            prevBtn.classList.toggle('cursor-not-allowed', isCurrentMonth);

            renderCalendarDays(); 
        }

        function initTimeDropdown() {
            const timeToggle = document.getElementById('timeToggle');
            const timeMenu = document.getElementById('timeMenu');

            timeToggle.addEventListener('click', (e) => { 
                e.stopPropagation(); 
                // Re-render whenever opened to show current state or "select date" message
                renderTimeSlots(currentTakenTimes);
                timeMenu.classList.toggle('hidden'); 
            });

            document.addEventListener('click', () => timeMenu.classList.add('hidden'));
        }

        async function fetchClinicSchedule() {
            try {
                const response = await fetch('/appointments/clinic-schedule');
                if (response.ok) {
                    clinicSchedule = await response.json();
                    if (selectedDate && isDateClosed(selectedDate)) resetDateTime();
                }
            } catch (error) { console.error('Schedule Load Error:', error); }
        }

        function isDateClosed(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            const dayOfWeek = date.getDay();
            if (clinicSchedule.opened_dates?.includes(dateStr)) return false;
            if (clinicSchedule.closed_dates?.includes(dateStr)) return true;
            return clinicSchedule.default_closed_days?.includes(dayOfWeek);
        }

        function selectDate(date) {
            selectedDate = date;
            document.getElementById('selectedDate').value = date;
            
            // Reset time when date changes
            document.getElementById('timeInput').value = '';
            document.getElementById('selectedTimeText').textContent = 'SELECT A TIME';
            
            renderCalendar();
            fetchTakenTimes(date);
        }

        function resetDateTime() {
            selectedDate = null;
            currentTakenTimes = [];
            document.getElementById('selectedDate').value = '';
            document.getElementById('timeInput').value = '';
            document.getElementById('selectedTimeText').textContent = 'SELECT A TIME';
        }

        function initServiceSelection() {
            document.querySelectorAll('.service-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.service-btn').forEach(b => b.classList.remove('border-red-700', 'bg-red-50'));
                    this.classList.add('border-red-700', 'bg-red-50');
                    this.querySelector('input').checked = true;
                });
            });
        }

        function initPetSelection() {
            document.querySelectorAll('.pet-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.pet-btn').forEach(b => b.classList.remove('border-red-700', 'bg-red-50'));
                    this.classList.add('border-red-700', 'bg-red-50');
                    this.querySelector('input').checked = true;
                });
            });
        }
    </script>
    </body>
    </html>
</x-dashboardheader-layout>