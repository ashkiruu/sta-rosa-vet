@extends('layouts.admin')

@section('page_title', 'Appointment Calendar')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Appointment Calendar</h1>
                <p class="text-sm text-gray-600">View and manage appointments by date. Click a date to open/close it.</p>
            </div>
            <div class="flex gap-3">
                <span class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 text-sm font-medium text-gray-700">
                    Total Appointments: {{ $appointments->count() }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Calendar Section --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    {{-- Calendar Header --}}
                    <div class="bg-red-700 text-white px-6 py-4">
                        <div class="flex justify-between items-center">
                            <button onclick="changeMonth(-1)" class="p-2 hover:bg-red-600 rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <h2 class="text-xl font-bold" id="currentMonth"></h2>
                            <button onclick="changeMonth(1)" class="p-2 hover:bg-red-600 rounded-lg transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Calendar Grid --}}
                    <div class="p-4">
                        {{-- Day Headers --}}
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            <div class="text-center text-xs font-semibold text-red-400 py-2">Sun</div>
                            <div class="text-center text-xs font-semibold text-gray-500 py-2">Mon</div>
                            <div class="text-center text-xs font-semibold text-gray-500 py-2">Tue</div>
                            <div class="text-center text-xs font-semibold text-gray-500 py-2">Wed</div>
                            <div class="text-center text-xs font-semibold text-gray-500 py-2">Thu</div>
                            <div class="text-center text-xs font-semibold text-gray-500 py-2">Fri</div>
                            <div class="text-center text-xs font-semibold text-red-400 py-2">Sat</div>
                        </div>
                        {{-- Calendar Days --}}
                        <div class="grid grid-cols-7 gap-1" id="calendarDays"></div>
                    </div>

                    {{-- Legend --}}
                    <div class="px-4 pb-4">
                        <div class="border-t pt-4">
                            <p class="text-xs font-semibold text-gray-500 mb-2">LEGEND</p>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded bg-gray-300"></span>
                                    <span class="text-gray-600">Closed</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                                    <span class="text-gray-600">Has Pending</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                    <span class="text-gray-600">Approved</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                    <span class="text-gray-600">Fully Booked</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="mt-6 bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-4">Today's Overview</h3>
                    @php
                        $todayStr = now()->format('Y-m-d');
                        $todayAppointments = $appointments->filter(function($appt) use ($todayStr) {
                            return $appt->Date === $todayStr;
                        });
                        $pendingToday = $todayAppointments->where('Status', 'Pending')->count();
                        $approvedToday = $todayAppointments->where('Status', 'Approved')->count();
                    @endphp
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Today</span>
                            <span class="font-bold text-gray-900">{{ $todayAppointments->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-yellow-600">Pending</span>
                            <span class="font-bold text-yellow-600">{{ $pendingToday }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-green-600">Approved</span>
                            <span class="font-bold text-green-600">{{ $approvedToday }}</span>
                        </div>
                    </div>
                </div>

                {{-- Schedule Management Info --}}
                <div class="mt-6 bg-blue-50 rounded-xl p-4 border border-blue-200">
                    <h4 class="font-semibold text-blue-800 mb-2">📅 Schedule Management</h4>
                    <p class="text-sm text-blue-700">Click on any date to open or close it for appointments. Saturdays and Sundays are closed by default.</p>
                </div>
            </div>

            {{-- Appointments List Section --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    {{-- Selected Date Header --}}
                    <div class="bg-gray-800 text-white px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold" id="selectedDateTitle">Select a date</h3>
                                <p class="text-gray-300 text-sm" id="selectedDateSubtitle">Click on a date to view appointments</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div id="appointmentCount" class="hidden">
                                    <span class="bg-white text-gray-800 px-3 py-1 rounded-full text-sm font-bold" id="countBadge">0</span>
                                </div>
                                <div id="toggleDateBtn" style="display: none;">
                                    {{-- Will be populated by JavaScript --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Time Slots Grid --}}
                    <div class="p-6" id="timeSlotsSection" style="display: none;">
                        <h4 class="font-semibold text-gray-700 mb-3">Time Slots Overview</h4>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 mb-6" id="timeSlotsGrid">
                            {{-- Time slots will be populated by JavaScript --}}
                        </div>
                        <div class="flex gap-4 text-xs text-gray-500 mb-4">
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span>
                                <span>Available</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-300"></span>
                                <span>Pending</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded bg-blue-100 border border-blue-300"></span>
                                <span>Approved</span>
                            </div>
                        </div>
                    </div>

                    {{-- Closed Day Message --}}
                    <div class="p-6 text-center" id="closedDayMessage" style="display: none;">
                        <div class="text-6xl mb-4">🚫</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Clinic Closed</h3>
                        <p class="text-gray-500 mb-4">This day is currently closed for appointments.</p>
                        <form id="openDayForm" method="POST" action="{{ route('admin.schedule.toggle') }}" class="inline">
                            @csrf
                            <input type="hidden" name="date" id="openDayDate">
                            <input type="hidden" name="action" value="open">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                🔓 Open This Day
                            </button>
                        </form>
                    </div>

                    {{-- Appointments List --}}
                    <div class="border-t" id="appointmentsContainer">
                        <div class="p-6 text-center text-gray-500" id="noDateSelected">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="font-medium">Select a date from the calendar</p>
                            <p class="text-sm">to view appointments for that day</p>
                        </div>

                        <div id="appointmentsList" class="divide-y divide-gray-200" style="display: none;">
                            {{-- Appointments will be populated by JavaScript --}}
                        </div>

                        <div id="noAppointments" class="p-6 text-center text-gray-500" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="font-medium">No appointments for this date</p>
                            <p class="text-sm">All time slots are available</p>
                            <form id="closeDayForm" method="POST" action="{{ route('admin.schedule.toggle') }}" class="inline mt-4">
                                @csrf
                                <input type="hidden" name="date" id="closeDayDate">
                                <input type="hidden" name="action" value="close">
                                <button type="submit" class="mt-4 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                    🔒 Close This Day
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Store appointments data from PHP
    const allAppointments = @json($appointments);
    
    // Store schedule configuration
    const schedule = @json($schedule);
    const defaultClosedDays = schedule.default_closed_days || [0, 6]; // Sunday = 0, Saturday = 6
    const openedDates = schedule.opened_dates || [];
    const closedDates = schedule.closed_dates || [];
    
    // Time slots configuration - must match the user booking page exactly
    const timeSlots = [
        '08:00', '08:10', '08:20', '08:30', '08:45',
        '09:00', '09:10', '09:20', '09:30', '09:40', '09:50',
        '10:00', '10:10', '10:20', '10:30', '10:40', '10:45'
    ];

    // Calendar state
    let currentDate = new Date();
    let selectedDate = null;

    // Check if a date is closed
    function isDateClosed(dateStr) {
        const date = new Date(dateStr + 'T00:00:00');
        const dayOfWeek = date.getDay();
        
        // Check if explicitly opened (overrides default closed days)
        if (openedDates.includes(dateStr)) {
            return false;
        }
        
        // Check if explicitly closed
        if (closedDates.includes(dateStr)) {
            return true;
        }
        
        // Check default closed days (weekends)
        return defaultClosedDays.includes(dayOfWeek);
    }

    // Initialize calendar
    function initCalendar() {
        renderCalendar();
    }

    // Render calendar
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Update header
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
        
        // Get first day of month and total days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Group appointments by date
        const appointmentsByDate = {};
        allAppointments.forEach(appt => {
            let date = appt.Date;
            if (date && date.includes('T')) {
                date = date.split('T')[0];
            }
            if (!appointmentsByDate[date]) {
                appointmentsByDate[date] = [];
            }
            appointmentsByDate[date].push(appt);
        });
        
        // Build calendar HTML
        let html = '';
        
        // Empty cells for days before first day of month
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="p-2"></div>';
        }
        
        // Days of the month
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayAppointments = appointmentsByDate[dateStr] || [];
            const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === day;
            const isSelected = selectedDate === dateStr;
            const isClosed = isDateClosed(dateStr);
            const dayOfWeek = new Date(dateStr + 'T00:00:00').getDay();
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            
            // Determine day status
            let statusClass = 'bg-gray-50 hover:bg-gray-100 text-gray-700';
            let dotHtml = '';
            
            if (isClosed) {
                // Closed day - grey out
                statusClass = 'bg-gray-200 text-gray-400 cursor-pointer hover:bg-gray-300';
                if (isWeekend) {
                    statusClass = 'bg-gray-300 text-gray-500 cursor-pointer hover:bg-gray-400';
                }
            } else if (dayAppointments.length > 0) {
                const hasPending = dayAppointments.some(a => a.Status === 'Pending');
                const hasApproved = dayAppointments.some(a => a.Status === 'Approved');
                const isFullyBooked = dayAppointments.length >= 17;
                
                if (isFullyBooked) {
                    statusClass = 'bg-red-100 hover:bg-red-200 text-red-800';
                    dotHtml = '<span class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-red-500"></span>';
                } else if (hasPending) {
                    statusClass = 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800';
                    dotHtml = '<span class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-yellow-500"></span>';
                } else if (hasApproved) {
                    statusClass = 'bg-green-100 hover:bg-green-200 text-green-800';
                    dotHtml = '<span class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-green-500"></span>';
                }
            }
            
            if (isToday && !isClosed) {
                statusClass = 'bg-red-600 text-white hover:bg-red-700';
                dotHtml = '';
            }
            
            if (isSelected) {
                statusClass = 'bg-gray-800 text-white hover:bg-gray-900 ring-2 ring-offset-2 ring-gray-800';
                dotHtml = '';
            }
            
            html += `
                <button 
                    onclick="selectDate('${dateStr}')" 
                    class="relative p-2 text-center rounded-lg font-medium transition ${statusClass}"
                    title="${isClosed ? 'Closed - Click to open' : 'Open - Click to view'}"
                >
                    ${day}
                    ${dotHtml}
                    ${dayAppointments.length > 0 && !isClosed ? `<span class="absolute top-0 right-0 text-[10px] font-bold">${dayAppointments.length}</span>` : ''}
                    ${isClosed ? '<span class="absolute top-0 right-0 text-[8px]">🔒</span>' : ''}
                </button>
            `;
        }
        
        document.getElementById('calendarDays').innerHTML = html;
    }

    // Change month
    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        renderCalendar();
    }

    // Select a date
    function selectDate(dateStr) {
        selectedDate = dateStr;
        renderCalendar();
        showAppointmentsForDate(dateStr);
    }

    // Show appointments for selected date
    function showAppointmentsForDate(dateStr) {
        const date = new Date(dateStr + 'T00:00:00');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const isClosed = isDateClosed(dateStr);
        
        document.getElementById('selectedDateTitle').textContent = date.toLocaleDateString('en-US', options);
        
        // Filter appointments for this date
        const dayAppointments = allAppointments.filter(appt => {
            let apptDate = appt.Date;
            if (apptDate && apptDate.includes('T')) {
                apptDate = apptDate.split('T')[0];
            }
            return apptDate === dateStr;
        });
        
        // Update hidden form fields for date toggle
        document.getElementById('openDayDate').value = dateStr;
        document.getElementById('closeDayDate').value = dateStr;
        
        if (isClosed) {
            // Show closed day message
            document.getElementById('selectedDateSubtitle').textContent = '🔒 Clinic is CLOSED';
            document.getElementById('appointmentCount').classList.add('hidden');
            document.getElementById('timeSlotsSection').style.display = 'none';
            document.getElementById('noDateSelected').style.display = 'none';
            document.getElementById('appointmentsList').style.display = 'none';
            document.getElementById('noAppointments').style.display = 'none';
            document.getElementById('closedDayMessage').style.display = 'block';
            document.getElementById('appointmentsContainer').style.display = 'none';
        } else {
            // Show appointments
            document.getElementById('closedDayMessage').style.display = 'none';
            document.getElementById('appointmentsContainer').style.display = 'block';
            
            document.getElementById('selectedDateSubtitle').textContent = 
                dayAppointments.length > 0 
                    ? `${dayAppointments.length} appointment(s) scheduled`
                    : 'No appointments scheduled - Clinic is OPEN';
            
            // Show count badge
            document.getElementById('appointmentCount').classList.remove('hidden');
            document.getElementById('countBadge').textContent = dayAppointments.length;
            
            // Show time slots section
            document.getElementById('timeSlotsSection').style.display = 'block';
            renderTimeSlots(dayAppointments);
            
            // Hide "no date selected" message
            document.getElementById('noDateSelected').style.display = 'none';
            
            if (dayAppointments.length === 0) {
                document.getElementById('appointmentsList').style.display = 'none';
                document.getElementById('noAppointments').style.display = 'block';
            } else {
                document.getElementById('noAppointments').style.display = 'none';
                document.getElementById('appointmentsList').style.display = 'block';
                renderAppointmentsList(dayAppointments);
            }
        }
    }

    // Render time slots grid
    function renderTimeSlots(dayAppointments) {
        const takenSlots = {};
        dayAppointments.forEach(appt => {
            let time = appt.Time;
            if (time && time.length > 5) {
                time = time.substring(0, 5);
            }
            if (time) {
                const [hours, minutes] = time.split(':');
                const normalizedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                takenSlots[normalizedTime] = appt.Status;
            }
        });
        
        let html = '';
        timeSlots.forEach(slot => {
            const status = takenSlots[slot];
            let slotClass = 'bg-green-50 border-green-200 text-green-700';
            let statusText = '';
            
            if (status === 'Pending') {
                slotClass = 'bg-yellow-50 border-yellow-300 text-yellow-700';
                statusText = '⏳';
            } else if (status === 'Approved') {
                slotClass = 'bg-blue-50 border-blue-300 text-blue-700';
                statusText = '✓';
            } else if (status === 'Cancelled') {
                slotClass = 'bg-gray-50 border-gray-200 text-gray-400 line-through';
            }
            
            const [hours, minutes] = slot.split(':');
            const hour12 = hours % 12 || 12;
            const ampm = hours < 12 ? 'AM' : 'PM';
            const displayTime = `${hour12}:${minutes}`;
            
            html += `
                <div class="text-center p-2 rounded border text-xs font-medium ${slotClass}" title="${slot} - ${status || 'Available'}">
                    ${displayTime} ${ampm} ${statusText}
                </div>
            `;
        });
        
        document.getElementById('timeSlotsGrid').innerHTML = html;
    }

    // Render appointments list
    function renderAppointmentsList(appointments) {
        appointments.sort((a, b) => a.Time.localeCompare(b.Time));
        
        let html = '';
        appointments.forEach(appt => {
            const timeParts = appt.Time.split(':');
            const hour12 = timeParts[0] % 12 || 12;
            const ampm = timeParts[0] < 12 ? 'AM' : 'PM';
            const formattedTime = `${hour12}:${timeParts[1]} ${ampm}`;
            
            let statusBadge = '';
            if (appt.Status === 'Pending') {
                statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span> Pending
                </span>`;
            } else if (appt.Status === 'Approved') {
                statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Approved
                </span>`;
            }
            
            let actionButtons = '';
            if (appt.Status === 'Pending') {
                actionButtons = `
                    <div class="flex gap-2 mt-2">
                        <form action="/admin/appointments/${appt.Appointment_ID}/approve" method="POST" class="inline">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition shadow-sm">
                                ✓ Approve
                            </button>
                        </form>
                        <form action="/admin/appointments/${appt.Appointment_ID}/reject" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to decline this appointment?');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="bg-white border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md text-xs font-medium transition">
                                ✗ Decline
                            </button>
                        </form>
                    </div>
                `;
            } else {
                actionButtons = '<p class="text-gray-400 text-xs italic mt-2">No actions available</p>';
            }
            
            html += `
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-16 text-center">
                                <div class="text-lg font-bold text-gray-900">${formattedTime.split(' ')[0]}</div>
                                <div class="text-xs text-gray-500">${formattedTime.split(' ')[1]}</div>
                            </div>
                            <div class="flex-shrink-0 h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-lg">
                                ${appt.user ? appt.user.First_Name.charAt(0) : '?'}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">
                                    ${appt.user ? appt.user.First_Name + ' ' + appt.user.Last_Name : 'Unknown User'}
                                </div>
                                <div class="text-sm text-indigo-600 font-medium">
                                    🐾 ${appt.pet ? appt.pet.Pet_Name : 'Unknown Pet'}
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        ${appt.service ? appt.service.Service_Name : 'Unknown Service'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="mb-2">${statusBadge}</div>
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('appointmentsList').innerHTML = html;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initCalendar();
        
        // Auto-select today
        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        selectDate(todayStr);
    });
</script>
@endsection