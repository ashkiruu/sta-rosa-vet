<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking</title>
    @vite([
        'resources/css/app.css',
        'resources/css/create_appointment.css',
        'resources/js/app.js'
    ])
    <style>
        /* Validation error styles */
        .validation-error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }
        .validation-error.show {
            display: block;
        }
        .field-error {
            border: 2px solid #dc2626 !important;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        .service-btn.field-error,
        .pet-btn.field-error {
            box-shadow: 0 0 0 2px #dc2626;
        }
        .calendar-container.field-error {
            border: 2px solid #dc2626;
        }
        .time-dropdown-toggle.field-error {
            border: 2px solid #dc2626 !important;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Breadcrumb -->
    <div class="text-gray-400 text-sm py-3 px-6 relative z-10">
        Appointment Booking
    </div>
    
    <!-- Header -->
    <nav class="bg-gradient-to-r from-red-800 to-red-700 text-white px-6 py-3 relative z-10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center overflow-hidden">
                    <span class="text-red-700 font-bold text-lg">🐾</span>
                </div>
                <div class="leading-tight">
                    <p class="text-xs text-red-200">City of</p>
                    <h1 class="font-bold text-lg">Veterinary</h1>
                    <p class="text-xs text-red-200">Office</p>
                </div>
            </div>
            <div class="flex gap-4 items-center">
                <a href="{{ route('dashboard') }}" class="text-white hover:underline text-sm">← Back to Dashboard</a>
                <button class="w-10 h-10 flex items-center justify-center text-white hover:bg-red-600 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
                <button class="w-10 h-10 flex items-center justify-center text-white hover:bg-red-600 rounded-lg transition border border-white/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 max-w-4xl pb-12 relative z-10">
        <div class="main-card">
            
            {{-- Server-side validation errors --}}
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <strong>Please fix the following errors:</strong>
                    </div>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('appointments.preview') }}" id="appointmentForm" novalidate>
                @csrf

                <!-- Service Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Service Type <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-4 bg-white/60 rounded-lg p-3" id="serviceContainer">
                        @foreach($services as $service)
                            <label class="service-btn" data-service="{{ $service->Service_ID }}">
                                <input type="radio" name="Service_ID" value="{{ $service->Service_ID }}" class="hidden" {{ old('Service_ID') == $service->Service_ID ? 'checked' : '' }}>
                                <span class="radio-dot"></span>
                                <span>{{ $service->Service_Name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="validation-error" id="serviceError">⚠️ Please select a service type for your appointment.</p>
                </div>

                <!-- Calendar and Time Section -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <!-- Calendar -->
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Date <span class="text-red-500">*</span></label>
                        <div class="calendar-container" id="calendarContainer">
                            <div class="flex justify-between items-center mb-4">
                                <button type="button" id="prevMonth" class="calendar-nav-btn">
                                    ←
                                </button>
                                <h3 class="text-sm font-semibold text-gray-700" id="monthYear">January 2026</h3>
                                <button type="button" id="nextMonth" class="calendar-nav-btn">
                                    →
                                </button>
                            </div>

                            <!-- Day Headers with Weekend Highlighting -->
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <div class="calendar-day-header text-red-400">Sun</div>
                                <div class="calendar-day-header">Mon</div>
                                <div class="calendar-day-header">Tue</div>
                                <div class="calendar-day-header">Wed</div>
                                <div class="calendar-day-header">Thu</div>
                                <div class="calendar-day-header">Fri</div>
                                <div class="calendar-day-header text-red-400">Sat</div>
                            </div>

                            <div id="calendarDays" class="grid grid-cols-7 gap-1"></div>
                            
                            <!-- Schedule Legend -->
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <div class="flex flex-wrap gap-3 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="w-3 h-3 rounded bg-gray-300"></span>
                                        <span class="text-gray-500">Closed</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="w-3 h-3 rounded bg-red-200"></span>
                                        <span class="text-gray-500">Fully Booked</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="validation-error" id="dateError">⚠️ Please select a date for your appointment.</p>
                        <input type="hidden" name="Date" id="selectedDate" value="{{ old('Date') }}">
                    </div>

                    <!-- Time Slot Dropdown -->
                    <div class="time-dropdown-wrapper">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Time <span class="text-red-500">*</span></label>
                        <div class="time-dropdown" id="timeDropdown">
                            <button type="button" class="time-dropdown-toggle" id="timeToggle">
                                <span id="selectedTimeText">Time</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <div class="time-dropdown-menu" id="timeMenu">
                                <div class="time-slot" data-value="08:00" data-display="08:00 AM">08:00 AM</div>
                                <div class="time-slot" data-value="08:10" data-display="08:10 AM">08:10 AM</div>
                                <div class="time-slot" data-value="08:20" data-display="08:20 AM">08:20 AM</div>
                                <div class="time-slot" data-value="08:30" data-display="08:30 AM">08:30 AM</div>
                                <div class="time-slot" data-value="08:45" data-display="08:45 AM">08:45 AM</div>
                                <div class="time-slot" data-value="09:00" data-display="09:00 AM">09:00 AM</div>
                                <div class="time-slot" data-value="09:10" data-display="09:10 AM">09:10 AM</div>
                                <div class="time-slot" data-value="09:20" data-display="09:20 AM">09:20 AM</div>
                                <div class="time-slot" data-value="09:30" data-display="09:30 AM">09:30 AM</div>
                                <div class="time-slot" data-value="09:40" data-display="09:40 AM">09:40 AM</div>
                                <div class="time-slot" data-value="09:50" data-display="09:50 AM">09:50 AM</div>
                                <div class="time-slot" data-value="10:00" data-display="10:00 AM">10:00 AM</div>
                                <div class="time-slot" data-value="10:10" data-display="10:10 AM">10:10 AM</div>
                                <div class="time-slot" data-value="10:20" data-display="10:20 AM">10:20 AM</div>
                                <div class="time-slot" data-value="10:30" data-display="10:30 AM">10:30 AM</div>
                                <div class="time-slot" data-value="10:40" data-display="10:40 AM">10:40 AM</div>
                                <div class="time-slot" data-value="10:45" data-display="10:45 AM">10:45 AM</div>
                            </div>
                            
                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="Time" id="timeInput" value="{{ old('Time') }}">
                        </div>
                        <p class="validation-error" id="timeError">⚠️ Please select a time for your appointment.</p>
                    </div>
                </div>

                <!-- Pet Selection -->
                <div class="mb-6">
                    <div class="pet-section-header">Choose Your Pet <span class="text-red-500">*</span></div>
                    
                    @if($pets->isEmpty())
                        <div class="pet-section-body text-center">
                            <p class="text-gray-600 mb-4">You don't have any registered pets yet.</p>
                            <a href="{{ route('pets.create') }}" class="add-pet-btn">
                                <span>➕</span>
                                <span>Register a Pet First</span>
                            </a>
                        </div>
                        <p class="validation-error show" id="noPetError">⚠️ You must register a pet before booking an appointment.</p>
                    @else
                        <div class="pet-section-body" id="petContainer">
                            <div class="flex flex-wrap gap-3">
                                @foreach($pets as $pet)
                                    <label class="pet-btn">
                                        <input type="radio" name="Pet_ID" value="{{ $pet->Pet_ID }}" class="hidden" {{ old('Pet_ID') == $pet->Pet_ID ? 'checked' : '' }}>
                                        <span class="pet-icon">🐕</span>
                                        <span>{{ $pet->Pet_Name }}</span>
                                    </label>
                                @endforeach
                                
                                <a href="{{ route('pets.create') }}" class="add-pet-btn">
                                    <span>➕</span>
                                    <span>Add Pet</span>
                                </a>
                            </div>
                        </div>
                        <p class="validation-error" id="petError">⚠️ Please select a pet for this appointment.</p>
                    @endif
                </div>

                <!-- Location (Hidden, default to office) -->
                <input type="hidden" name="Location" value="Veterinary Office">
                <input type="hidden" name="Special_Notes" value="">

                <!-- Submit Button -->
                <div class="text-right">
                    @if($pets->isEmpty())
                        <button type="button" class="next-btn opacity-50 cursor-not-allowed" disabled title="Please register a pet first">
                            Next
                        </button>
                    @else
                        <button type="submit" class="next-btn" id="submitBtn">
                            Next
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <script>
        // =====================================================
        // CLINIC SCHEDULE CONFIGURATION
        // =====================================================
        let clinicSchedule = {
            default_closed_days: [0, 6], // Sunday = 0, Saturday = 6
            opened_dates: [],
            closed_dates: []
        };
        let scheduleLoaded = false;

        // Fetch clinic schedule on page load
        async function fetchClinicSchedule() {
            try {
                const response = await fetch('/appointments/clinic-schedule');
                if (response.ok) {
                    clinicSchedule = await response.json();
                    console.log('Clinic schedule loaded:', clinicSchedule);
                    scheduleLoaded = true;
                    
                    // If a date was already selected, check if it's now closed
                    if (selectedDate && isDateClosed(selectedDate)) {
                        selectedDate = null;
                        document.getElementById('selectedDate').value = '';
                        timeInput.value = '';
                        selectedTimeText.textContent = 'Time';
                        timeSlots.forEach(s => s.classList.remove('selected'));
                    }
                }
            } catch (error) {
                console.error('Failed to load clinic schedule:', error);
                scheduleLoaded = true;
            }
        }

        // Check if a date is closed
        function isDateClosed(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            const dayOfWeek = date.getDay();
            
            if (clinicSchedule.opened_dates && clinicSchedule.opened_dates.includes(dateStr)) {
                return false;
            }
            
            if (clinicSchedule.closed_dates && clinicSchedule.closed_dates.includes(dateStr)) {
                return true;
            }
            
            return clinicSchedule.default_closed_days && clinicSchedule.default_closed_days.includes(dayOfWeek);
        }

        // =====================================================
        // VALIDATION FUNCTIONS
        // =====================================================
        function clearAllErrors() {
            document.querySelectorAll('.validation-error').forEach(el => {
                if (el.id !== 'noPetError') { // Don't hide the "no pet" error
                    el.classList.remove('show');
                }
            });
            document.querySelectorAll('.field-error').forEach(el => {
                el.classList.remove('field-error');
            });
        }

        function showError(elementId, errorId) {
            const element = document.getElementById(elementId);
            const error = document.getElementById(errorId);
            if (element) element.classList.add('field-error');
            if (error) error.classList.add('show');
        }

        function hideError(elementId, errorId) {
            const element = document.getElementById(elementId);
            const error = document.getElementById(errorId);
            if (element) element.classList.remove('field-error');
            if (error) error.classList.remove('show');
        }

        function validateForm() {
            let isValid = true;
            clearAllErrors();

            // Validate Service
            const serviceSelected = document.querySelector('input[name="Service_ID"]:checked');
            if (!serviceSelected) {
                showError('serviceContainer', 'serviceError');
                isValid = false;
            }

            // Validate Date
            const dateValue = document.getElementById('selectedDate').value;
            if (!dateValue) {
                showError('calendarContainer', 'dateError');
                isValid = false;
            } else if (isDateClosed(dateValue)) {
                showError('calendarContainer', 'dateError');
                document.getElementById('dateError').textContent = '⚠️ The selected date is closed. Please choose another date.';
                isValid = false;
            }

            // Validate Time
            const timeValue = document.getElementById('timeInput').value;
            if (!timeValue) {
                showError('timeToggle', 'timeError');
                isValid = false;
            }

            // Validate Pet
            const petSelected = document.querySelector('input[name="Pet_ID"]:checked');
            if (!petSelected) {
                const petContainer = document.getElementById('petContainer');
                if (petContainer) {
                    petContainer.classList.add('field-error');
                }
                showError('petContainer', 'petError');
                isValid = false;
            }

            return isValid;
        }

        // =====================================================
        // SERVICE BUTTON TOGGLE
        // =====================================================
        document.querySelectorAll('.service-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.service-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input[type="radio"]').checked = true;
                hideError('serviceContainer', 'serviceError');
            });

            // Check if already selected (from old input)
            const radio = btn.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                btn.classList.add('active');
            }
        });

        // =====================================================
        // PET BUTTON TOGGLE
        // =====================================================
        document.querySelectorAll('.pet-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.pet-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
                hideError('petContainer', 'petError');
                const petContainer = document.getElementById('petContainer');
                if (petContainer) petContainer.classList.remove('field-error');
            });

            // Check if already selected (from old input)
            const radio = btn.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                btn.classList.add('active');
            }
        });

        // =====================================================
        // TIME DROPDOWN FUNCTIONALITY
        // =====================================================
        const timeToggle = document.getElementById('timeToggle');
        const timeMenu = document.getElementById('timeMenu');
        const timeInput = document.getElementById('timeInput');
        const selectedTimeText = document.getElementById('selectedTimeText');
        const timeSlots = document.querySelectorAll('.time-slot');

        // Toggle dropdown
        timeToggle.addEventListener('click', function() {
            this.classList.toggle('open');
            timeMenu.classList.toggle('open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.time-dropdown')) {
                timeToggle.classList.remove('open');
                timeMenu.classList.remove('open');
            }
        });

        // Handle time slot selection
        timeSlots.forEach(slot => {
            slot.addEventListener('click', function() {
                if (this.classList.contains('taken')) {
                    return;
                }
                
                timeSlots.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                
                const value = this.dataset.value;
                const display = this.dataset.display;
                timeInput.value = value;
                selectedTimeText.textContent = display;
                
                timeToggle.classList.remove('open');
                timeMenu.classList.remove('open');
                
                hideError('timeToggle', 'timeError');
            });
        });

        // Restore old time value if exists
        if (timeInput.value) {
            const oldTimeSlot = document.querySelector(`.time-slot[data-value="${timeInput.value}"]`);
            if (oldTimeSlot) {
                oldTimeSlot.classList.add('selected');
                selectedTimeText.textContent = oldTimeSlot.dataset.display;
            }
        }

        // =====================================================
        // FETCH TAKEN TIMES FOR A DATE
        // =====================================================
        function fetchTakenTimes(date) {
            if (!date) return;
            
            fetch(`/appointments/taken-times?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    timeSlots.forEach(slot => {
                        slot.classList.remove('taken');
                    });
                    
                    data.takenTimes.forEach(time => {
                        const slot = document.querySelector(`.time-slot[data-value="${time}"]`);
                        if (slot) {
                            slot.classList.add('taken');
                            if (slot.classList.contains('selected')) {
                                slot.classList.remove('selected');
                                timeInput.value = '';
                                selectedTimeText.textContent = 'Time';
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching taken times:', error);
                });
        }

        // =====================================================
        // CALENDAR FUNCTIONALITY
        // =====================================================
        let currentDate = new Date();
        let selectedDate = document.getElementById('selectedDate').value || null;
        let fullyBookedDates = [];

        const totalTimeSlots = 17;

        async function checkFullyBookedDates(year, month) {
            fullyBookedDates = [];
            
            try {
                const response = await fetch(`/appointments/fully-booked?year=${year}&month=${month + 1}`);
                const data = await response.json();
                fullyBookedDates = data.fullyBookedDates || [];
            } catch (error) {
                console.error('Error fetching fully booked dates:', error);
            }
            
            renderCalendarDays();
        }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            document.getElementById('monthYear').textContent = 
                new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            checkFullyBookedDates(year, month);
        }

        function renderCalendarDays() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let daysHTML = '';
            
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = prevMonthDays - i;
                daysHTML += `<div class="calendar-day other-month">${day}</div>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dateObj = new Date(year, month, day);
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isPast = dateObj < today;
                const isSelected = selectedDate === dateStr;
                const isFullyBooked = fullyBookedDates.includes(dateStr);
                const isClosed = isDateClosed(dateStr);
                
                let classes = 'calendar-day';
                let title = '';
                let isDisabled = false;
                
                if (isPast) {
                    classes += ' past';
                    title = 'Past date';
                    isDisabled = true;
                } else if (isClosed) {
                    classes += ' closed';
                    title = 'Clinic closed';
                    isDisabled = true;
                } else if (isFullyBooked) {
                    classes += ' fully-booked';
                    title = 'Fully booked';
                    isDisabled = true;
                }
                
                if (isSelected && !isDisabled) {
                    classes += ' selected';
                    title = 'Selected';
                }
                
                daysHTML += `<div class="${classes}" data-date="${dateStr}" data-disabled="${isDisabled}" title="${title}">${day}</div>`;
            }

            const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
            const remainingCells = totalCells - (firstDay + daysInMonth);
            for (let day = 1; day <= remainingCells; day++) {
                daysHTML += `<div class="calendar-day other-month">${day}</div>`;
            }

            document.getElementById('calendarDays').innerHTML = daysHTML;

            document.querySelectorAll('.calendar-day').forEach(dayEl => {
                dayEl.addEventListener('click', function(e) {
                    if (this.classList.contains('other-month')) {
                        return;
                    }
                    
                    const isDisabled = this.getAttribute('data-disabled') === 'true';
                    
                    if (isDisabled) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        if (this.classList.contains('closed')) {
                            alert('This day is closed. The clinic is not open on this date.');
                        } else if (this.classList.contains('fully-booked')) {
                            alert('This day is fully booked. Please select another date.');
                        } else if (this.classList.contains('past')) {
                            alert('You cannot book appointments for past dates.');
                        }
                        return false;
                    }
                    
                    document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedDate = this.dataset.date;
                    document.getElementById('selectedDate').value = selectedDate;
                    
                    fetchTakenTimes(selectedDate);
                    
                    timeSlots.forEach(s => s.classList.remove('selected'));
                    timeInput.value = '';
                    selectedTimeText.textContent = 'Time';
                    
                    hideError('calendarContainer', 'dateError');
                });
            });
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            const today = new Date();
            const newDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1);
            
            if (newDate >= new Date(today.getFullYear(), today.getMonth(), 1)) {
                currentDate = newDate;
                renderCalendar();
            }
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        // =====================================================
        // FORM SUBMISSION WITH VALIDATION
        // =====================================================
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = document.querySelector('.validation-error.show');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                return false;
            }
            
            return true;
        });

        // =====================================================
        // INITIALIZE
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            fetchClinicSchedule().then(() => {
                renderCalendar();
                
                // If there's an old date value, fetch its taken times
                if (selectedDate) {
                    fetchTakenTimes(selectedDate);
                }
            });
        });
    </script>
</body>
</html>