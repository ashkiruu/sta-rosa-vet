<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a;
        }
        
        /* Paw print background pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cellipse cx='40' cy='28' rx='6' ry='8'/%3E%3Cellipse cx='26' cy='36' rx='5' ry='6'/%3E%3Cellipse cx='54' cy='36' rx='5' ry='6'/%3E%3Cellipse cx='30' cy='50' rx='4' ry='5'/%3E%3Cellipse cx='50' cy='50' rx='4' ry='5'/%3E%3Cellipse cx='40' cy='58' rx='10' ry='8'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }
        
        .service-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }
        
        .service-btn .radio-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .service-btn .radio-dot::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: transparent;
        }
        
        .service-btn.active .radio-dot {
            border-color: #dc2626;
        }
        
        .service-btn.active .radio-dot::after {
            background: #dc2626;
        }
        
        .service-btn span {
            color: #374151;
        }
        
        .calendar-container {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .calendar-nav-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #fecaca;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        
        .calendar-nav-btn:hover {
            background: #fca5a5;
        }
        
        .calendar-day-header {
            font-size: 11px;
            font-weight: 500;
            color: #6b7280;
            text-align: center;
            padding: 4px 0;
        }
        
        .calendar-day {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s;
            margin: auto;
        }
        
        .calendar-day:hover:not(.disabled):not(.past) {
            background: #fee2e2;
        }
        
        .calendar-day.selected {
            background: #f87171;
            color: white;
        }
        
        .calendar-day.disabled,
        .calendar-day.past {
            color: #d1d5db;
            cursor: not-allowed;
        }
        
        .calendar-day.other-month {
            color: #9ca3af;
        }
        
        .time-dropdown {
            background: #fbbf24;
            border-radius: 8px;
            padding: 12px 16px;
            height: fit-content;
        }
        
        .time-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        
        .pet-section-header {
            background: #fbbf24;
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 16px;
            border-radius: 8px 8px 0 0;
        }
        
        .pet-section-body {
            background: white;
            padding: 16px;
            border-radius: 0 0 8px 8px;
        }
        
        .pet-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid #fecaca;
            background: #fff1f2;
            color: #991b1b;
        }
        
        .pet-btn:hover {
            background: #fee2e2;
        }
        
        .pet-btn.active {
            background: #dc2626;
            border-color: #b91c1c;
            color: white;
        }
        
        .pet-btn .pet-icon {
            font-size: 16px;
        }
        
        .add-pet-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            border: 2px solid #fecaca;
            background: #fff1f2;
            color: #991b1b;
            text-decoration: none;
        }
        
        .add-pet-btn:hover {
            background: #fee2e2;
        }
        
        .next-btn {
            background: #dc2626;
            color: white;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .next-btn:hover {
            background: #b91c1c;
        }
        
        .main-card {
            background: linear-gradient(135deg, #fecdd3 0%, #fda4af 100%);
            border-radius: 16px;
            padding: 24px;
            position: relative;
            z-index: 1;
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

                        <div class="grid grid-cols-7 gap-1 mb-2">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>

                        <div id="calendarDays" class="grid grid-cols-7 gap-1"></div>
                        
                        <input type="hidden" name="Date" id="selectedDate" required>
                    </div>

                    <!-- Time Slot Dropdown -->
                    <div class="time-dropdown md:w-48">
                        <select name="Time" id="Time" class="time-select" required>
                            <option value="">Time</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                        </select>
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
        // Service button toggle
        document.querySelectorAll('.service-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.service-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Pet button toggle
        document.querySelectorAll('.pet-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.pet-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });

        // Calendar functionality
        let currentDate = new Date();
        let selectedDate = null;

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            document.getElementById('monthYear').textContent = 
                new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let daysHTML = '';
            
            // Previous month days
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = prevMonthDays - i;
                daysHTML += `<div class="calendar-day other-month disabled">${day}</div>`;
            }

            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const dateObj = new Date(year, month, day);
                const dateStr = dateObj.toISOString().split('T')[0];
                const isPast = dateObj < today;
                const isSelected = selectedDate === dateStr;
                
                let classes = 'calendar-day';
                if (isPast) classes += ' past disabled';
                if (isSelected) classes += ' selected';
                
                daysHTML += `<div class="${classes}" data-date="${dateStr}">${day}</div>`;
            }

            // Next month days to fill the grid
            const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
            const remainingCells = totalCells - (firstDay + daysInMonth);
            for (let day = 1; day <= remainingCells; day++) {
                daysHTML += `<div class="calendar-day other-month disabled">${day}</div>`;
            }

            document.getElementById('calendarDays').innerHTML = daysHTML;

            // Add click handlers to future dates only
            document.querySelectorAll('.calendar-day:not(.disabled)').forEach(day => {
                day.addEventListener('click', function() {
                    document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedDate = this.dataset.date;
                    document.getElementById('selectedDate').value = selectedDate;
                });
            });
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            const today = new Date();
            const newDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1);
            
            // Don't allow going to past months
            if (newDate >= new Date(today.getFullYear(), today.getMonth(), 1)) {
                currentDate = newDate;
                renderCalendar();
            }
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        renderCalendar();
    </script>
</body>
</html>