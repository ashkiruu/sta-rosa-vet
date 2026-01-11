<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Pet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a;
            min-height: 100vh;
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
        
        .main-card {
            background: linear-gradient(135deg, #e8b4b8 0%, #d4a0a5 100%);
            border-radius: 16px;
            padding: 24px;
            position: relative;
            z-index: 1;
        }
        
        .form-section-header {
            background: #fbbf24;
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 16px;
            border-radius: 8px 8px 0 0;
        }
        
        .form-section-body {
            background: white;
            padding: 24px;
            border-radius: 0 0 8px 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 20px;
            background: #f3f4f6;
            font-size: 14px;
            color: #374151;
            outline: none;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            background: #e5e7eb;
            box-shadow: 0 0 0 2px #fbbf24;
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 20px;
            background: #f3f4f6;
            font-size: 14px;
            color: #374151;
            outline: none;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
        }
        
        .form-select:focus {
            background-color: #e5e7eb;
            box-shadow: 0 0 0 2px #fbbf24;
        }
        
        .back-btn {
            width: 48px;
            height: 48px;
            background: #fbbf24;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #92400e;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.2s;
        }
        
        .back-btn:hover {
            background: #f59e0b;
        }
        
        .submit-btn {
            background: #dc2626;
            color: white;
            padding: 12px 48px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .submit-btn:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
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

    <div class="container mx-auto mt-8 px-4 max-w-2xl pb-12 relative z-10">
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

            <form method="POST" action="{{ route('pets.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Pet Information Section -->
                <div class="mb-6">
                    <div class="form-section-header">Pet Information</div>
                    <div class="form-section-body">
                        
                        <!-- Pet Name and Sex Row -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="form-label">Pet Name</label>
                                <input 
                                    type="text" 
                                    name="Pet_Name" 
                                    class="form-input" 
                                    placeholder="Batumbakal"
                                    value="{{ old('Pet_Name') }}"
                                    required
                                >
                            </div>
                            <div>
                                <label class="form-label">Sex ( M or F )</label>
                                <select name="Sex" class="form-select" required>
                                    <option value="" disabled {{ old('Sex') ? '' : 'selected' }}>Select</option>
                                    <option value="Male" {{ old('Sex') == 'Male' ? 'selected' : '' }}>M</option>
                                    <option value="Female" {{ old('Sex') == 'Female' ? 'selected' : '' }}>F</option>
                                </select>
                            </div>
                        </div>

                        <!-- Age and Species Row -->
<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="form-label">Age ( months )</label>
        <input 
            type="number" 
            name="Age" 
            class="form-input" 
            placeholder="23"
            value="{{ old('Age') }}"
            min="0"
            required
        >
    </div>
    <div>
        <label class="form-label">Species</label>
        <select name="Species_ID" id="speciesSelect" class="form-select" required>
    <option value="" disabled {{ old('Species_ID') ? '' : 'selected' }}>Select</option>
    @foreach($species as $sp)
        <option value="{{ $sp->Species_ID }}" {{ old('Species_ID') == $sp->Species_ID ? 'selected' : '' }}>
            {{ $sp->Species_Name }}
        </option>
    @endforeach
</select>
    </div>
</div>

<!-- Other Species (conditional) -->
<div class="mb-4" id="otherSpeciesDiv" style="display: none;">
    <label class="form-label">If other species ( <span class="italic text-gray-500">Please specify</span> )</label>
    <input 
        type="text" 
        name="other_species" 
        class="form-input" 
        placeholder="Enter species name"
        value="{{ old('other_species') }}"
    >
</div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center">
                    <a href="{{ route('pets.index') }}" class="back-btn">
                        ←
                    </a>
                    <button type="submit" class="submit-btn">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const speciesSelect = document.getElementById('speciesSelect');
    const otherSpeciesDiv = document.getElementById('otherSpeciesDiv');
    
    speciesSelect.addEventListener('change', function() {
        // Check if selected option text is "Other"
        const selectedText = this.options[this.selectedIndex].text;
        if (selectedText === 'Other') {
            otherSpeciesDiv.style.display = 'block';
        } else {
            otherSpeciesDiv.style.display = 'none';
        }
    });

    // Check on page load
    const selectedText = speciesSelect.options[speciesSelect.selectedIndex]?.text;
    if (selectedText === 'Other') {
        otherSpeciesDiv.style.display = 'block';
    }
</script>
</body>
</html>