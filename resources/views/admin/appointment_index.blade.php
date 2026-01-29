@extends('layouts.admin')

@section('page_title', 'Appointment Calendar')

@section('content')
<div class="min-h-screen py-4">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">Clinic Schedule</h1>
            <p class="text-[10px] font-bold text-red-600 uppercase tracking-[0.2em]">Manage daily bookings and availability</p>
        </div>
        
        <div class="bg-white px-6 py-3 rounded-[1.5rem] shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-10 h-10 bg-red-50 text-red-600 rounded-xl flex items-center justify-center shadow-sm">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Total Bookings</p>
                <p class="text-xl font-black text-gray-900 leading-none mt-1">{{ $appointments->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Same-Day Booking Policy Notice --}}
    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
        <i class="fas fa-info-circle mr-3"></i>
        <span class="text-[10px] font-black uppercase tracking-widest">Policy: Same-day appointments are disabled. All bookings must be scheduled at least one day in advance.</span>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-exclamation-triangle mr-3"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-info-circle mr-3"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ session('info') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Left Column: Calendar & Stats --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Calendar Card --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-900 text-white px-8 py-6 flex justify-between items-center">
                    <button onclick="changeMonth(-1)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-700 rounded-xl transition text-white/70 hover:text-white">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <h2 class="text-xs font-black uppercase tracking-[0.2em]" id="currentMonth"></h2>
                    <button onclick="changeMonth(1)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-700 rounded-xl transition text-white/70 hover:text-white">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-7 gap-1 mb-4">
                        <div class="text-center text-[9px] font-black text-red-400 uppercase py-2">Sun</div>
                        <div class="text-center text-[9px] font-black text-gray-400 uppercase py-2">Mon</div>
                        <div class="text-center text-[9px] font-black text-gray-400 uppercase py-2">Tue</div>
                        <div class="text-center text-[9px] font-black text-gray-400 uppercase py-2">Wed</div>
                        <div class="text-center text-[9px] font-black text-gray-400 uppercase py-2">Thu</div>
                        <div class="text-center text-[9px] font-black text-gray-400 uppercase py-2">Fri</div>
                        <div class="text-center text-[9px] font-black text-red-400 uppercase py-2">Sat</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1" id="calendarDays"></div>
                </div>

                <div class="px-6 pb-6 pt-2 border-t border-gray-50 bg-gray-50/30">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Status Legend</p>
                    <div class="grid grid-cols-2 gap-y-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400 border border-amber-500"></span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Today (Locked)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Closed</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-yellow-400 shadow-[0_0_5px_rgba(250,204,21,0.5)]"></span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Has Pending</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_5px_rgba(34,197,94,0.5)]"></span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Approved</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]"></span>
                            <span class="text-[9px] font-bold text-gray-500 uppercase">Full</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Today's Overview Stats --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-4 border-b border-gray-50 pb-3">Daily Snapshot</h3>
                
                @php
                    $todayStr = now()->format('Y-m-d');
                    $todayAppointments = $appointments->filter(function($appt) use ($todayStr) {
                        return $appt->Date === $todayStr;
                    });
                    $pendingToday = $todayAppointments->where('Status', 'Pending')->count();
                    $approvedToday = $todayAppointments->where('Status', 'Approved')->count();
                    $qrReleasedToday = $todayAppointments->where('qr_released', true)->count();
                @endphp

                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Total Today</span>
                        <span class="text-xl font-black text-gray-900 leading-none">{{ $todayAppointments->count() }}</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-[10px] font-bold text-yellow-600 uppercase">Pending Review</span>
                        <span class="text-xl font-black text-yellow-500 leading-none">{{ $pendingToday }}</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-[10px] font-bold text-green-600 uppercase">Confirmed</span>
                        <span class="text-xl font-black text-green-500 leading-none">{{ $approvedToday }}</span>
                    </div>
                    <div class="flex justify-between items-end pt-2 border-t border-gray-50">
                        <span class="text-[10px] font-bold text-blue-600 uppercase">QR Sent</span>
                        <span class="text-xl font-black text-blue-500 leading-none">{{ $qrReleasedToday }}</span>
                    </div>
                </div>
            </div>

            {{-- Guide --}}
            <div class="bg-blue-50 rounded-[1.5rem] p-6 border border-blue-100">
                <h4 class="text-[10px] font-black text-blue-800 uppercase tracking-widest mb-2 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Manager Tip
                </h4>
                <p class="text-[10px] font-bold text-blue-600 leading-relaxed uppercase">
                    Click any date to view details. Today is always locked for new bookings per clinic policy.
                </p>
            </div>
        </div>

        {{-- Right Column: Detailed List --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden min-h-[600px] flex flex-col">
                
                {{-- Dynamic Date Header --}}
                <div class="bg-white border-b border-gray-50 px-8 py-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight" id="selectedDateTitle">Select a Date</h3>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1" id="selectedDateSubtitle">View schedule details</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div id="appointmentCount" class="hidden">
                            <span class="bg-gray-900 text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm" id="countBadge">0</span>
                        </div>
                        
                        {{-- Today Indicator Badge --}}
                        <div id="todayBadge" class="hidden">
                            <span class="bg-amber-100 text-amber-700 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border border-amber-200">
                                <i class="fas fa-lock mr-1"></i> No New Bookings
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Time Slots Matrix --}}
                <div class="p-8 border-b border-gray-50 bg-gray-50/30" id="timeSlotsSection" style="display: none;">
                    <h4 class="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-4">Availability Matrix</h4>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 mb-6" id="timeSlotsGrid"></div>
                    
                    <div class="flex flex-wrap gap-4 pt-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-green-100 border border-green-200"></span>
                            <span class="text-[9px] font-black text-green-700 uppercase tracking-wider">Open</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-200"></span>
                            <span class="text-[9px] font-black text-yellow-700 uppercase tracking-wider">Pending</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-blue-100 border border-blue-200"></span>
                            <span class="text-[9px] font-black text-blue-700 uppercase tracking-wider">Approved</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-gray-200 border border-gray-300"></span>
                            <span class="text-[9px] font-black text-gray-500 uppercase tracking-wider">Locked (Today)</span>
                        </div>
                    </div>
                </div>

                {{-- Closed State --}}
                <div class="flex-1 flex flex-col justify-center items-center p-12 hidden" id="closedDayMessage">
                    <div class="w-20 h-20 bg-red-50 rounded-[2rem] flex items-center justify-center mb-6 text-red-400">
                        <i class="fas fa-lock text-3xl"></i>
                    </div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-2">Clinic Closed</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-6 tracking-widest">No appointments can be booked</p>
                    
                    <form id="openDayForm" method="POST" action="{{ route('admin.schedule.toggle') }}">
                        @csrf
                        <input type="hidden" name="date" id="openDayDate">
                        <input type="hidden" name="action" value="open">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition shadow-lg shadow-green-200 flex items-center gap-2">
                            <i class="fas fa-unlock"></i> Open Schedule
                        </button>
                    </form>
                </div>

                {{-- Appointments Container --}}
                <div id="appointmentsContainer" class="flex-1 flex flex-col">
                    
                    {{-- Empty State --}}
                    <div class="flex-1 flex flex-col justify-center items-center p-12 text-center" id="noDateSelected">
                        <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-6 border border-gray-100">
                            <i class="fas fa-mouse-pointer text-3xl text-gray-300"></i>
                        </div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Select a date to begin</p>
                    </div>

                    {{-- List --}}
                    <div id="appointmentsList" class="divide-y divide-gray-50 w-full hidden"></div>

                    {{-- No Appointments State --}}
                    <div id="noAppointments" class="flex-1 flex flex-col justify-center items-center p-12 text-center hidden">
                        <div class="w-20 h-20 bg-green-50 rounded-[2rem] flex items-center justify-center mb-6 text-green-400">
                            <i class="fas fa-check text-3xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-1" id="noAppointmentsTitle">Schedule Clear</p>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-6" id="noAppointmentsSubtitle">No bookings for this date</p>
                        
                        <form id="closeDayForm" method="POST" action="{{ route('admin.schedule.toggle') }}">
                            @csrf
                            <input type="hidden" name="date" id="closeDayDate">
                            <input type="hidden" name="action" value="close">
                            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition shadow-lg flex items-center gap-2">
                                <i class="fas fa-lock"></i> Close Schedule
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const allAppointments = @json($appointments);
    const schedule = @json($schedule);
    const defaultClosedDays = schedule.default_closed_days || [0, 6]; 
    const openedDates = schedule.opened_dates || [];
    const closedDates = schedule.closed_dates || [];
    
    const timeSlots = [
        '08:00', '08:10', '08:20', '08:30', '08:45',
        '09:00', '09:10', '09:20', '09:30', '09:40', '09:50',
        '10:00', '10:10', '10:20', '10:30', '10:40', '10:45'
    ];

    let currentDate = new Date();
    let selectedDate = null;

    // Get today's date string for comparison
    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    function isDateClosed(dateStr) {
        const date = new Date(dateStr + 'T00:00:00');
        const dayOfWeek = date.getDay();
        if (openedDates.includes(dateStr)) return false;
        if (closedDates.includes(dateStr)) return true;
        return defaultClosedDays.includes(dayOfWeek);
    }

    function isToday(dateStr) {
        return dateStr === todayStr;
    }

    function isPastDate(dateStr) {
        const checkDate = new Date(dateStr + 'T00:00:00');
        const todayDate = new Date();
        todayDate.setHours(0, 0, 0, 0);
        return checkDate < todayDate;
    }

    function initCalendar() { renderCalendar(); }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const appointmentsByDate = {};
        allAppointments.forEach(appt => {
            let date = appt.Date.split('T')[0];
            if (!appointmentsByDate[date]) appointmentsByDate[date] = [];
            appointmentsByDate[date].push(appt);
        });
        
        let html = '';
        for (let i = 0; i < firstDay; i++) html += '<div class="p-2"></div>';
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayAppointments = appointmentsByDate[dateStr] || [];
            const isTodayDate = isToday(dateStr);
            const isSelected = selectedDate === dateStr;
            const isClosed = isDateClosed(dateStr);
            const isPast = isPastDate(dateStr);
            
            let statusClass = 'bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-xs';
            let dotHtml = '';
            let iconHtml = '';
            
            // TODAY: Special amber styling - viewable but locked for new bookings
            if (isTodayDate) {
                statusClass = 'bg-amber-100 text-amber-700 hover:bg-amber-200 border-2 border-amber-300 shadow-sm';
                iconHtml = '<i class="fas fa-clock absolute top-0.5 right-0.5 text-[6px] text-amber-500"></i>';
            } else if (isClosed || isPast) {
                statusClass = 'bg-gray-100 text-gray-300 cursor-pointer hover:bg-gray-200 text-xs font-medium';
                if (isClosed && !isPast) {
                    iconHtml = '<i class="fas fa-lock absolute top-1 right-1 text-[6px]"></i>';
                }
            } else if (dayAppointments.length > 0) {
                const hasPending = dayAppointments.some(a => a.Status === 'Pending');
                const hasApproved = dayAppointments.some(a => a.Status === 'Approved');
                const isFullyBooked = dayAppointments.length >= 17;
                
                if (isFullyBooked) {
                    statusClass = 'bg-red-50 hover:bg-red-100 text-red-600 border border-red-100';
                    dotHtml = '<span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-red-500"></span>';
                } else if (hasPending) {
                    statusClass = 'bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-100';
                    dotHtml = '<span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-yellow-500"></span>';
                } else if (hasApproved) {
                    statusClass = 'bg-green-50 hover:bg-green-100 text-green-700 border border-green-100';
                    dotHtml = '<span class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-green-500"></span>';
                }
            }
            
            if (isSelected) {
                statusClass = 'bg-red-600 text-white hover:bg-red-700 shadow-lg ring-2 ring-red-200 ring-offset-2';
            }
            
            html += `
                <button onclick="selectDate('${dateStr}')" class="relative h-10 w-full rounded-xl transition-all ${statusClass}">
                    ${day}
                    ${dotHtml}
                    ${dayAppointments.length > 0 && !isClosed ? `<span class="absolute -top-1 -right-1 w-4 h-4 bg-white text-gray-900 rounded-full text-[8px] font-black flex items-center justify-center shadow-sm border border-gray-100">${dayAppointments.length}</span>` : ''}
                    ${iconHtml}
                </button>
            `;
        }
        document.getElementById('calendarDays').innerHTML = html;
    }

    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        renderCalendar();
    }

    function selectDate(dateStr) {
        selectedDate = dateStr;
        renderCalendar();
        showAppointmentsForDate(dateStr);
    }

    function showAppointmentsForDate(dateStr) {
        const date = new Date(dateStr + 'T00:00:00');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const isClosed = isDateClosed(dateStr);
        const isTodayDate = isToday(dateStr);
        const isPast = isPastDate(dateStr);
        
        document.getElementById('selectedDateTitle').textContent = date.toLocaleDateString('en-US', options);
        
        const dayAppointments = allAppointments.filter(appt => {
            return (appt.Date && appt.Date.split('T')[0] === dateStr);
        });
        
        document.getElementById('openDayDate').value = dateStr;
        document.getElementById('closeDayDate').value = dateStr;
        
        // Hide all states first
        document.getElementById('closedDayMessage').classList.add('hidden');
        document.getElementById('closedDayMessage').style.display = 'none';
        document.getElementById('noDateSelected').style.display = 'none';
        document.getElementById('appointmentsList').style.display = 'none';
        document.getElementById('noAppointments').style.display = 'none';
        document.getElementById('todayBadge').classList.add('hidden');
        document.getElementById('closeDayForm').style.display = 'block';
        
        if (isClosed && !isTodayDate) {
            // Regular closed day (not today)
            document.getElementById('selectedDateSubtitle').textContent = 'Clinic Operations Suspended';
            document.getElementById('appointmentCount').classList.add('hidden');
            document.getElementById('timeSlotsSection').style.display = 'none';
            document.getElementById('closedDayMessage').classList.remove('hidden');
            document.getElementById('closedDayMessage').style.display = 'flex';
            document.getElementById('appointmentsContainer').style.display = 'none';
        } else {
            document.getElementById('appointmentsContainer').style.display = 'flex';
            
            // Show "today" badge if it's today
            if (isTodayDate) {
                document.getElementById('todayBadge').classList.remove('hidden');
                document.getElementById('selectedDateSubtitle').textContent = 'Today - View existing appointments only';
                document.getElementById('closeDayForm').style.display = 'none';
            } else {
                document.getElementById('selectedDateSubtitle').textContent = 
                    dayAppointments.length > 0 ? 'Managing scheduled visits' : 'Schedule is open for bookings';
            }
            
            document.getElementById('appointmentCount').classList.remove('hidden');
            document.getElementById('countBadge').textContent = dayAppointments.length + ' Bookings';
            
            document.getElementById('timeSlotsSection').style.display = 'block';
            renderTimeSlots(dayAppointments, isTodayDate);
            
            if (dayAppointments.length === 0) {
                document.getElementById('appointmentsList').style.display = 'none';
                document.getElementById('noAppointments').style.display = 'flex';
                
                // Update no appointments message for today
                if (isTodayDate) {
                    document.getElementById('noAppointmentsTitle').textContent = 'No Appointments Today';
                    document.getElementById('noAppointmentsSubtitle').textContent = 'Same-day bookings are not accepted';
                } else {
                    document.getElementById('noAppointmentsTitle').textContent = 'Schedule Clear';
                    document.getElementById('noAppointmentsSubtitle').textContent = 'No bookings for this date';
                }
            } else {
                document.getElementById('noAppointments').style.display = 'none';
                document.getElementById('appointmentsList').style.display = 'block';
                renderAppointmentsList(dayAppointments);
            }
        }
    }

    function renderTimeSlots(dayAppointments, isTodayDate = false) {
        const takenSlots = {};
        dayAppointments.forEach(appt => {
            let time = appt.Time;
            if (time) {
                const [h, m] = time.substring(0, 5).split(':');
                takenSlots[`${h}:${m}`] = appt.Status;
            }
        });
        
        let html = '';
        timeSlots.forEach(slot => {
            const status = takenSlots[slot];
            let slotClass = 'bg-gray-50 border-gray-200 text-gray-400';
            
            if (!status) {
                // If today, show available slots as "locked"
                if (isTodayDate) {
                    slotClass = 'bg-gray-200 border-gray-300 text-gray-400';
                } else {
                    slotClass = 'bg-green-50 border-green-200 text-green-700';
                }
            } else if (status === 'Pending') {
                slotClass = 'bg-yellow-50 border-yellow-200 text-yellow-700';
            } else if (status === 'Approved') {
                slotClass = 'bg-blue-50 border-blue-200 text-blue-700';
            }
            
            const [h, m] = slot.split(':');
            const ampm = h < 12 ? 'AM' : 'PM';
            const displayTime = `${h % 12 || 12}:${m}`;
            
            html += `
                <div class="text-center py-2 rounded-xl border text-[10px] font-black ${slotClass}">
                    ${displayTime} ${ampm}
                </div>
            `;
        });
        document.getElementById('timeSlotsGrid').innerHTML = html;
    }

    function renderAppointmentsList(appointments) {
        appointments.sort((a, b) => a.Time.localeCompare(b.Time));
        
        let html = '';
        appointments.forEach(appt => {
            const [h, m] = appt.Time.substring(0, 5).split(':');
            const ampm = h < 12 ? 'AM' : 'PM';
            
            let statusBadge = '';
            let actionButtons = '';
            
            if (appt.Status === 'Pending') {
                statusBadge = `<span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black bg-yellow-50 text-yellow-700 border border-yellow-100 uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-2 animate-pulse"></span> Pending</span>`;
                
                actionButtons = `
                    <div class="flex gap-2 mt-3">
                        <form action="/admin/appointments/${appt.Appointment_ID}/approve" method="POST" class="flex-1">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm">Approve</button>
                        </form>
                        <form action="/admin/appointments/${appt.Appointment_ID}/reject" method="POST" class="flex-1" onsubmit="return confirm('Decline this appointment?');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="w-full bg-white border border-red-100 text-red-600 hover:bg-red-50 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition">Decline</button>
                        </form>
                    </div>
                `;
            } else if (appt.Status === 'Approved') {
                statusBadge = `<span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black bg-green-50 text-green-700 border border-green-100 uppercase tracking-widest"><span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span> Approved</span>`;
                
                if (appt.qr_released) {
                    actionButtons = `<div class="mt-3 p-2 bg-blue-50 rounded-xl text-center border border-blue-100"><p class="text-[9px] font-black text-blue-600 uppercase tracking-widest"><i class="fas fa-check-circle mr-1"></i> QR Sent</p></div>`;
                } else {
                    actionButtons = `
                        <form action="/admin/appointments/${appt.Appointment_ID}/release-qr" method="POST" class="mt-3">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="w-full bg-gray-900 hover:bg-blue-600 text-white py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm flex items-center justify-center gap-2">
                                <i class="fas fa-qrcode"></i> Release QR
                            </button>
                        </form>
                    `;
                }
            }

            html += `
                <div class="p-6 hover:bg-gray-50/50 transition group">
                    <div class="flex flex-col sm:flex-row justify-between gap-6">
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center justify-center w-14 h-14 bg-gray-100 rounded-2xl text-gray-900 font-black border border-gray-200">
                                <span class="text-lg leading-none">${h % 12 || 12}</span>
                                <span class="text-[9px] uppercase tracking-widest text-gray-500">${ampm}</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight">${appt.user ? appt.user.First_Name + ' ' + appt.user.Last_Name : 'Unknown User'}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">🐾 ${appt.pet ? appt.pet.Pet_Name : 'Unknown Pet'}</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wide bg-blue-50 px-2 py-0.5 rounded-lg">${appt.service ? appt.service.Service_Name : 'Service'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-full sm:w-48 flex flex-col items-end">
                            ${statusBadge}
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            `;
        });
        document.getElementById('appointmentsList').innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCalendar();
        selectDate(todayStr);
    });
</script>
@endsection