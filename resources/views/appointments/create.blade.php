<x-dashboardheader-layout>
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
    </head>
    <body class="min-h-screen">
        <!-- Breadcrumb -->
        <div class="text-gray-400 text-sm py-3 px-6 relative z-10">
            Appointment Booking
        </div>

        <div class="container mx-auto mt-8 px-4 max-w-4xl pb-12 relative z-10">
            <div class="main-card">
                
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('appointments.preview') }}" id="appointmentForm">
                    @csrf

                    <!-- Service Type Selection -->
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-4 bg-white/60 rounded-lg p-3">
                            @foreach($services as $service)
                                <label class="service-btn" data-service="{{ $service->Service_ID }}">
                                    <input type="radio" name="Service_ID" value="{{ $service->Service_ID }}" class="hidden" required>
                                    <span class="radio-dot"></span>
                                    <span>{{ $service->Service_Name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Calendar and Time Section -->
                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <!-- Calendar -->
                        <div class="calendar-container flex-1">
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
                            
                            <input type="hidden" name="Date" id="selectedDate" required>
                        </div>

                        <!-- Time Slot Dropdown -->
                        <div class="time-dropdown-wrapper">
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
                                <input type="hidden" name="Time" id="timeInput" required>
                            </div>
                        </div>
                    </div>

                    <!-- Pet Selection -->
                    <div class="mb-6">
                        <div class="pet-section-header">Choose Your Pet</div>
                        
                        @if($pets->isEmpty())
                            <div class="pet-section-body text-center">
                                <p class="text-gray-600 mb-4">You don't have any registered pets yet.</p>
                                <a href="{{ route('pets.create') }}" class="add-pet-btn">
                                    <span>➕</span>
                                    <span>Register a Pet</span>
                                </a>
                            </div>
                        @else
                            <div class="pet-section-body">
                                <div class="flex flex-wrap gap-3">
                                    @foreach($pets as $pet)
                                        <label class="pet-btn">
                                            <input type="radio" name="Pet_ID" value="{{ $pet->Pet_ID }}" class="hidden" required>
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
                        @endif
                    </div>

                    <!-- Location (Hidden, default to office) -->
                    <input type="hidden" name="Location" value="Veterinary Office">
                    <input type="hidden" name="Special_Notes" value="">

                    <!-- Submit Button -->
                    <div class="text-right">
                        <button type="submit" class="next-btn">
                            Next
                        </button>
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
                        console.log('Default closed days:', clinicSchedule.default_closed_days);
                        console.log('Opened dates:', clinicSchedule.opened_dates);
                        console.log('Closed dates:', clinicSchedule.closed_dates);
                        scheduleLoaded = true;
                        
                        // If a date was already selected, check if it's now closed
                        if (selectedDate && isDateClosed(selectedDate)) {
                            console.log('Previously selected date is now closed, clearing selection');
                            selectedDate = null;
                            document.getElementById('selectedDate').value = '';
                            // Reset time selection too
                            timeInput.value = '';
                            selectedTimeText.textContent = 'Time';
                            timeSlots.forEach(s => s.classList.remove('selected'));
                        }
                    }
                } catch (error) {
                    console.error('Failed to load clinic schedule:', error);
                    scheduleLoaded = true; // Still mark as loaded to use defaults
                }
            }

            // Check if a date is closed
            function isDateClosed(dateStr) {
                const date = new Date(dateStr + 'T00:00:00');
                const dayOfWeek = date.getDay();
                
                // Check if explicitly opened (overrides default closed days)
                if (clinicSchedule.opened_dates && clinicSchedule.opened_dates.includes(dateStr)) {
                    return false;
                }
                
                // Check if explicitly closed
                if (clinicSchedule.closed_dates && clinicSchedule.closed_dates.includes(dateStr)) {
                    return true;
                }
                
                // Check default closed days (weekends)
                return clinicSchedule.default_closed_days && clinicSchedule.default_closed_days.includes(dayOfWeek);
            }

            // =====================================================
            // SERVICE BUTTON TOGGLE
            // =====================================================
            document.querySelectorAll('.service-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.service-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    this.querySelector('input[type="radio"]').checked = true;
                });
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
                });
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
                        return; // Don't allow selection of taken slots
                    }
                    
                    // Remove selected class from all slots
                    timeSlots.forEach(s => s.classList.remove('selected'));
                    
                    // Add selected class to clicked slot
                    this.classList.add('selected');
                    
                    // Update hidden input and display text
                    const value = this.dataset.value;
                    const display = this.dataset.display;
                    timeInput.value = value;
                    selectedTimeText.textContent = display;
                    
                    // Close dropdown
                    timeToggle.classList.remove('open');
                    timeMenu.classList.remove('open');
                });
            });

            // =====================================================
            // FETCH TAKEN TIMES FOR A DATE
            // =====================================================
            function fetchTakenTimes(date) {
                if (!date) return;
                
                fetch(`/appointments/taken-times?date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        // Reset all slots
                        timeSlots.forEach(slot => {
                            slot.classList.remove('taken');
                        });
                        
                        // Mark taken slots
                        data.takenTimes.forEach(time => {
                            const slot = document.querySelector(`.time-slot[data-value="${time}"]`);
                            if (slot) {
                                slot.classList.add('taken');
                                // If this was selected, deselect it
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
            let selectedDate = null;
            let fullyBookedDates = [];

            const totalTimeSlots = 17;

            // Check fully booked dates for the current month
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
                
                // Previous month days
                for (let i = firstDay - 1; i >= 0; i--) {
                    const day = prevMonthDays - i;
                    daysHTML += `<div class="calendar-day other-month">${day}</div>`;
                }

                // Current month days
                for (let day = 1; day <= daysInMonth; day++) {
                    const dateObj = new Date(year, month, day);
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const isPast = dateObj < today;
                    const isSelected = selectedDate === dateStr;
                    const isFullyBooked = fullyBookedDates.includes(dateStr);
                    
                    // Check if date is closed using clinic schedule
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

                // Next month days to fill the grid
                const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
                const remainingCells = totalCells - (firstDay + daysInMonth);
                for (let day = 1; day <= remainingCells; day++) {
                    daysHTML += `<div class="calendar-day other-month">${day}</div>`;
                }

                document.getElementById('calendarDays').innerHTML = daysHTML;

                // Add click handlers
                document.querySelectorAll('.calendar-day').forEach(dayEl => {
                    dayEl.addEventListener('click', function(e) {
                        // Skip other month days
                        if (this.classList.contains('other-month')) {
                            return;
                        }
                        
                        // Check if disabled using data attribute
                        const isDisabled = this.getAttribute('data-disabled') === 'true';
                        
                        if (isDisabled) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Show feedback based on reason
                            if (this.classList.contains('closed')) {
                                alert('This day is closed. The clinic is not open on this date.');
                            } else if (this.classList.contains('fully-booked')) {
                                alert('This day is fully booked. Please select another date.');
                            } else if (this.classList.contains('past')) {
                                alert('You cannot book appointments for past dates.');
                            }
                            return false;
                        }
                        
                        // Normal selection for enabled days
                        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                        this.classList.add('selected');
                        selectedDate = this.dataset.date;
                        document.getElementById('selectedDate').value = selectedDate;
                        
                        // Fetch taken times when date is selected
                        fetchTakenTimes(selectedDate);
                        
                        // Reset time selection when date changes
                        timeSlots.forEach(s => s.classList.remove('selected'));
                        timeInput.value = '';
                        selectedTimeText.textContent = 'Time';
                    });
                });
            }

            // Previous month button
            document.getElementById('prevMonth').addEventListener('click', () => {
                const today = new Date();
                const newDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1);
                
                if (newDate >= new Date(today.getFullYear(), today.getMonth(), 1)) {
                    currentDate = newDate;
                    renderCalendar();
                }
            });

            // Next month button
            document.getElementById('nextMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            // =====================================================
            // INITIALIZE
            // =====================================================
            // First fetch clinic schedule, then render calendar
            document.addEventListener('DOMContentLoaded', function() {
                fetchClinicSchedule().then(() => {
                    renderCalendar();
                });
                
                // Add form submission validation
                document.getElementById('appointmentForm').addEventListener('submit', function(e) {
                    const selectedDateValue = document.getElementById('selectedDate').value;
                    
                    // Check if date is selected
                    if (!selectedDateValue) {
                        e.preventDefault();
                        alert('Please select a date for your appointment.');
                        return false;
                    }
                    
                    // Check if selected date is closed
                    if (isDateClosed(selectedDateValue)) {
                        e.preventDefault();
                        alert('The selected date is closed. Please choose another date.');
                        // Clear the invalid selection
                        selectedDate = null;
                        document.getElementById('selectedDate').value = '';
                        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                        renderCalendarDays();
                        return false;
                    }
                    
                    // Check if time is selected
                    if (!timeInput.value) {
                        e.preventDefault();
                        alert('Please select a time for your appointment.');
                        return false;
                    }
                    
                    return true;
                });
            });
        </script>
    </body>
    </html>
</x-dashboardheader-layout>