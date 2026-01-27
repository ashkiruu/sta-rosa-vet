@extends('layouts.admin')

@section('page_title', isset($certificate) ? 'Edit Certificate' : 'Generate Certificate')

@section('content')
<div class="min-h-screen py-6 bg-gray-50/50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">
                    {{ isset($certificate) ? 'Edit Record' : 'Issue Certificate' }}
                </h1>
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mt-1">
                    {{ isset($certificate) ? 'Update existing documentation' : 'Create new official medical record' }}
                </p>
            </div>
            
            <a href="{{ route('admin.certificates.index') }}" class="group inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Return to List
            </a>
        </div>

        {{-- Error Handling --}}
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl mb-8 shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <span class="text-xs font-black text-red-700 uppercase tracking-widest">Submission Errors</span>
                </div>
                <ul class="list-disc list-inside text-sm font-medium text-red-600 space-y-1 ml-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $serviceType = $certificate['service_type'] ?? $appointment->service->Service_Name ?? '';
            $isVaccination = stripos($serviceType, 'vaccination') !== false || stripos($serviceType, 'vaccine') !== false;
            $isDeworming = stripos($serviceType, 'deworming') !== false;
            $isCheckup = stripos($serviceType, 'checkup') !== false || stripos($serviceType, 'check-up') !== false;
            
            $vaccineType = old('vaccine_type', $certificate['vaccine_type'] ?? '');
            $vaccineUsed = old('vaccine_used', $certificate['vaccine_used'] ?? '');
            
            if (empty($vaccineType) && !empty($vaccineUsed)) {
                $vaccineType = stripos($vaccineUsed, 'rabies') !== false ? 'anti-rabies' : 'other';
            }
            
            $petBreed = old('pet_breed', $certificate['pet_breed'] ?? $appointment->pet->Breed ?? '');
            $petColor = old('pet_color', $certificate['pet_color'] ?? $appointment->pet->Color ?? '');
            $breedMissing = empty($petBreed);
            $colorMissing = empty($petColor);
            
            // ✅ FIX: Properly pull user data for civil status, residency, and birthdate
            $user = $appointment->user ?? null;
            $defaultCivilStatus = old('civil_status', $certificate['civil_status'] ?? ($user->Civil_Status ?? ''));
            $defaultYearsOfResidency = old('years_of_residency', $certificate['years_of_residency'] ?? ($user->Years_Of_Residency ?? ''));
            $defaultOwnerBirthdate = old('owner_birthdate', $certificate['owner_birthdate'] ?? ($user->Birthdate ? $user->Birthdate->format('Y-m-d') : ''));
        @endphp

        {{-- Missing Info Alert --}}
        @if($breedMissing || $colorMissing)
            <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl mb-8 flex items-start gap-4 shadow-sm">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center shrink-0 text-amber-500">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-800 uppercase tracking-widest mb-1">Incomplete Profile Data</h4>
                    <p class="text-sm font-medium text-amber-700">
                        Please manually input the following missing fields:
                        @if($breedMissing) <span class="font-bold underline decoration-amber-400">Breed</span> @endif
                        @if($breedMissing && $colorMissing) & @endif
                        @if($colorMissing) <span class="font-bold underline decoration-amber-400">Color</span> @endif
                    </p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ isset($certificate) ? route('admin.certificates.update', $certificate['id']) : route('admin.certificates.store') }}">
            @csrf
            @if(isset($certificate)) @method('PUT') @endif
            
            <input type="hidden" name="appointment_id" value="{{ $appointment->Appointment_ID ?? $certificate['appointment_id'] }}">
            <input type="hidden" name="service_type" value="{{ $serviceType }}">

            {{-- Service Overview Card --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-indigo-600 px-8 py-5 flex items-center justify-between">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Service Context</h2>
                    <i class="fas fa-file-medical text-white/50 text-xl"></i>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Service Rendered</label>
                            <div class="w-full px-5 py-3 bg-indigo-50/50 border border-indigo-100 rounded-xl text-indigo-900 font-bold text-sm flex items-center gap-2">
                                <i class="fas fa-check-circle text-indigo-500"></i> {{ $serviceType }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Appointment Date</label>
                            <div class="w-full px-5 py-3 bg-gray-50 border border-gray-100 rounded-xl text-gray-700 font-bold text-sm">
                                {{ isset($appointment) ? \Carbon\Carbon::parse($appointment->Date)->format('F d, Y') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pet Information Card --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-blue-600 px-8 py-5 flex items-center justify-between">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Pet Profile</h2>
                    <i class="fas fa-paw text-white/50 text-xl"></i>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Pet Name <span class="text-red-500">*</span></label>
                            <input type="text" name="pet_name" required
                                   value="{{ old('pet_name', $certificate['pet_name'] ?? $appointment->pet->Pet_Name ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Species Type <span class="text-red-500">*</span></label>
                            <select name="animal_type" required class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer">
                                <option value="">Select Type</option>
                                @php $animalType = old('animal_type', $certificate['animal_type'] ?? ($appointment->pet->species->Species_Name ?? '')); @endphp
                                @foreach(['Dog', 'Cat', 'Bird', 'Rabbit', 'Others'] as $type)
                                    <option value="{{ $type }}" {{ $animalType == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Gender <span class="text-red-500">*</span></label>
                            <select name="pet_gender" required class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer">
                                <option value="">Select Gender</option>
                                @php $petGender = old('pet_gender', $certificate['pet_gender'] ?? $appointment->pet->Sex ?? ''); @endphp
                                <option value="Male" {{ $petGender == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $petGender == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Age <span class="text-red-500">*</span></label>
                            <input type="text" name="pet_age" required placeholder="e.g., 2 years, 6 months"
                                   value="{{ old('pet_age', $certificate['pet_age'] ?? $appointment->pet->Age ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">
                                Breed <span class="text-red-500">*</span>
                                @if($breedMissing) <span class="ml-2 text-[9px] text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md">REQUIRED</span> @endif
                            </label>
                            <input type="text" name="pet_breed" required
                                   placeholder="{{ $breedMissing ? 'Please fill in the breed (e.g., Labrador, Aspin, Mixed)' : '' }}"
                                   value="{{ $petBreed }}"
                                   class="w-full px-5 py-3 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all {{ $breedMissing ? 'bg-amber-50 ring-2 ring-amber-200' : 'bg-gray-50' }}">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">
                                Color <span class="text-red-500">*</span>
                                @if($colorMissing) <span class="ml-2 text-[9px] text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md">REQUIRED</span> @endif
                            </label>
                            <input type="text" name="pet_color" required
                                   placeholder="{{ $colorMissing ? 'Please fill in the color (e.g., Brown, Black & White)' : '' }}"
                                   value="{{ $petColor }}"
                                   class="w-full px-5 py-3 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all {{ $colorMissing ? 'bg-amber-50 ring-2 ring-amber-200' : 'bg-gray-50' }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Date of Birth</label>
                            <input type="date" name="pet_dob"
                                   value="{{ old('pet_dob', $certificate['pet_dob'] ?? ($appointment->pet->Date_of_Birth ? $appointment->pet->Date_of_Birth->format('Y-m-d') : '')) }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Owner Information Card --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-green-600 px-8 py-5 flex items-center justify-between">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Pet Owner Details</h2>
                    <i class="fas fa-user text-white/50 text-xl"></i>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="owner_name" required
                                   value="{{ old('owner_name', $certificate['owner_name'] ?? (($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Middle_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? ''))) }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Complete Address <span class="text-red-500">*</span></label>
                            @php
                                $defaultAddress = '';
                                if (isset($appointment->user)) {
                                    $defaultAddress = $appointment->user->Address ?? '';
                                    if ($appointment->user->barangay) {
                                        $defaultAddress .= ($defaultAddress ? ', ' : '') . 'Brgy. ' . $appointment->user->barangay->Barangay_Name;
                                    }
                                }
                            @endphp
                            <textarea name="owner_address" required rows="2"
                                      class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all resize-none">{{ old('owner_address', $certificate['owner_address'] ?? $defaultAddress) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Civil Status <span class="text-red-500">*</span></label>
                            <select name="civil_status" required class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer">
                                <option value="">Select Status</option>
                                @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $status)
                                    <option value="{{ $status }}" {{ $defaultCivilStatus == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Residency Length <span class="text-red-500">*</span></label>
                            <input type="text" name="years_of_residency" required placeholder="e.g., 5 years, Since birth"
                                   value="{{ $defaultYearsOfResidency }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Contact No. <span class="text-red-500">*</span></label>
                            <input type="text" name="owner_phone" required
                                   value="{{ old('owner_phone', $certificate['owner_phone'] ?? $appointment->user->Contact_Number ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Birthdate</label>
                            <input type="date" name="owner_birthdate"
                                   value="{{ $defaultOwnerBirthdate }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clinical Data Card --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-purple-600 px-8 py-5 flex items-center justify-between">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Clinical Records</h2>
                    <i class="fas fa-notes-medical text-white/50 text-xl"></i>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Service Date <span class="text-red-500">*</span></label>
                            <input type="date" name="service_date" required
                                   value="{{ old('service_date', $certificate['service_date'] ?? $certificate['vaccination_date'] ?? (isset($appointment) ? \Carbon\Carbon::parse($appointment->Date)->format('Y-m-d') : '')) }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Next Follow-up</label>
                            <input type="date" name="next_service_date"
                                   value="{{ old('next_service_date', $certificate['next_service_date'] ?? $certificate['next_vaccination_date'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all">
                        </div>

                        @if($isVaccination)
                            <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Vaccine Classification <span class="text-red-500">*</span></label>
                                <select name="vaccine_type" id="vaccine_type" required onchange="toggleVaccineFields()"
                                        class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer">
                                    <option value="">Select Category</option>
                                    <option value="anti-rabies" {{ $vaccineType == 'anti-rabies' ? 'selected' : '' }}>Anti-Rabies</option>
                                    <option value="other" {{ $vaccineType == 'other' ? 'selected' : '' }}>Other Vaccine</option>
                                </select>
                            </div>

                            {{-- Anti-Rabies --}}
                            <div id="antiRabiesFields" class="md:col-span-2 {{ $vaccineType != 'anti-rabies' ? 'hidden' : '' }}">
                                <div class="bg-purple-50 p-6 rounded-[1.5rem] border border-purple-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-purple-400 uppercase tracking-widest mb-2 ml-1">Brand Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="vaccine_name_rabies" id="vaccine_name_rabies" 
                                                   value="{{ old('vaccine_name_rabies', ($vaccineType == 'anti-rabies' ? ($vaccineUsed ?: 'Anti-Rabies Vaccine') : 'Anti-Rabies Vaccine')) }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-purple-900 text-sm transition-all" 
                                                   placeholder="e.g., Anti-Rabies Vaccine, Rabisin, Nobivac Rabies">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-purple-400 uppercase tracking-widest mb-2 ml-1">Lot / Batch No. <span class="text-red-500">*</span></label>
                                            <input type="text" name="lot_number" id="lot_number_rabies" 
                                                   value="{{ old('lot_number', $certificate['lot_number'] ?? '') }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-purple-900 text-sm transition-all font-mono" 
                                                   placeholder="e.g., LOT-2024-001">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Other Vaccines --}}
                            <div id="otherVaccineFields" class="md:col-span-2 {{ $vaccineType != 'other' ? 'hidden' : '' }}">
                                <div class="bg-yellow-50 p-6 rounded-[1.5rem] border border-yellow-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-2 ml-1">Vaccine Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="vaccine_name_other" id="vaccine_name_other" 
                                                   value="{{ old('vaccine_name_other', ($vaccineType == 'other' ? $vaccineUsed : '')) }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-yellow-500 font-bold text-yellow-900 text-sm transition-all" 
                                                   placeholder="Enter vaccine name (e.g., 5-in-1, Parvovirus, etc.)">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-2 ml-1">Lot / Batch No. <span class="text-red-500">*</span></label>
                                            <input type="text" name="lot_number_other" id="lot_number_other" 
                                                   value="{{ old('lot_number_other', ($vaccineType == 'other' ? ($certificate['lot_number'] ?? '') : '')) }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-yellow-500 font-bold text-yellow-900 text-sm transition-all font-mono" 
                                                   placeholder="e.g., LOT-2024-001">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="vaccine_used" id="vaccine_used_final" value="{{ $vaccineUsed }}">
                            <input type="hidden" name="lot_number_final" id="lot_number_final" value="{{ old('lot_number', $certificate['lot_number'] ?? '') }}">

                        @elseif($isDeworming)
                            <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Medication Used</label>
                                <input type="text" name="medicine_used" 
                                       value="{{ old('medicine_used', $certificate['medicine_used'] ?? $certificate['vaccine_used'] ?? '') }}"
                                       class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all" 
                                       placeholder="e.g., Albendazole, Pyrantel Pamoate">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Dosage</label>
                                <input type="text" name="dosage" 
                                       value="{{ old('dosage', $certificate['dosage'] ?? '') }}"
                                       class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all" 
                                       placeholder="e.g., 1 tablet, 5ml">
                            </div>
                        @else
                            <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Findings / Remarks</label>
                                <textarea name="findings" rows="3" class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all resize-none" 
                                          placeholder="Enter checkup findings or remarks...">{{ old('findings', $certificate['findings'] ?? '') }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Recommendations</label>
                                <textarea name="recommendations" rows="2" class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all resize-none" 
                                          placeholder="Enter recommendations if any...">{{ old('recommendations', $certificate['recommendations'] ?? '') }}</textarea>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Veterinarian Info --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-red-600 px-8 py-5 flex items-center justify-between">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Authorized Signatory</h2>
                    <i class="fas fa-signature text-white/50 text-xl"></i>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Veterinarian Name <span class="text-red-500">*</span></label>
                            <input type="text" name="veterinarian_name" required
                                   value="{{ old('veterinarian_name', $certificate['veterinarian_name'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-500 font-bold text-gray-700 text-sm transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">License No. <span class="text-red-500">*</span></label>
                            <input type="text" name="license_number" required
                                   value="{{ old('license_number', $certificate['license_number'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-500 font-bold text-gray-700 text-sm transition-all font-mono" 
                                   placeholder="e.g., VET-12345">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">PTR No. <span class="text-red-500">*</span></label>
                            <input type="text" name="ptr_number" required
                                   value="{{ old('ptr_number', $certificate['ptr_number'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-500 font-bold text-gray-700 text-sm transition-all font-mono" 
                                   placeholder="e.g., PTR-2024-001">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.certificates.index') }}" 
                   class="px-8 py-4 bg-white border border-gray-200 text-gray-600 rounded-[1.5rem] hover:bg-gray-50 text-[10px] font-black uppercase tracking-widest text-center transition shadow-sm">
                    Discard Changes
                </a>
                <button type="submit" name="action" value="draft"
                        class="px-8 py-4 bg-amber-400 text-amber-900 rounded-[1.5rem] hover:bg-amber-500 text-[10px] font-black uppercase tracking-widest transition shadow-lg shadow-amber-100 flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Save Draft
                </button>
                <button type="submit" name="action" value="approve"
                        class="px-8 py-4 bg-green-600 text-white rounded-[1.5rem] hover:bg-green-700 text-[10px] font-black uppercase tracking-widest transition shadow-lg shadow-green-200 flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Finalize & Approve
                </button>
            </div>
        </form>
    </div>
</div>

@if($isVaccination)
<script>
    function toggleVaccineFields() {
        const vaccineType = document.getElementById('vaccine_type').value;
        const antiRabiesFields = document.getElementById('antiRabiesFields');
        const otherVaccineFields = document.getElementById('otherVaccineFields');
        
        if (vaccineType === 'anti-rabies') {
            antiRabiesFields.classList.remove('hidden');
            otherVaccineFields.classList.add('hidden');
            
            document.getElementById('vaccine_name_rabies').setAttribute('required', 'required');
            document.getElementById('lot_number_rabies').setAttribute('required', 'required');
            document.getElementById('vaccine_name_other').removeAttribute('required');
            document.getElementById('lot_number_other').removeAttribute('required');
            
        } else if (vaccineType === 'other') {
            antiRabiesFields.classList.add('hidden');
            otherVaccineFields.classList.remove('hidden');
            
            document.getElementById('vaccine_name_rabies').removeAttribute('required');
            document.getElementById('lot_number_rabies').removeAttribute('required');
            document.getElementById('vaccine_name_other').setAttribute('required', 'required');
            document.getElementById('lot_number_other').setAttribute('required', 'required');
            
        } else {
            antiRabiesFields.classList.add('hidden');
            otherVaccineFields.classList.add('hidden');
            
            document.getElementById('vaccine_name_rabies')?.removeAttribute('required');
            document.getElementById('lot_number_rabies')?.removeAttribute('required');
            document.getElementById('vaccine_name_other')?.removeAttribute('required');
            document.getElementById('lot_number_other')?.removeAttribute('required');
        }
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        const vaccineType = document.getElementById('vaccine_type')?.value;
        const vaccineUsedFinal = document.getElementById('vaccine_used_final');
        const lotNumberFinal = document.getElementById('lot_number_final');
        
        if (vaccineType === 'anti-rabies') {
            vaccineUsedFinal.value = document.getElementById('vaccine_name_rabies').value;
            lotNumberFinal.value = document.getElementById('lot_number_rabies').value;
        } else if (vaccineType === 'other') {
            vaccineUsedFinal.value = document.getElementById('vaccine_name_other').value;
            lotNumberFinal.value = document.getElementById('lot_number_other').value;
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        toggleVaccineFields();
    });
</script>
@endif
@endsection