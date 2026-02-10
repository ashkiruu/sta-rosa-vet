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

        {{-- Error Summary --}}
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl mb-8 shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <span class="text-xs font-black text-red-700 uppercase tracking-widest">Please fix the following errors</span>
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
            $breedMissing = empty($petBreed) && !$errors->has('pet_breed');
            $colorMissing = empty($petColor) && !$errors->has('pet_color');
            
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

        <form method="POST" action="{{ isset($certificate) ? route('admin.certificates.update', $certificate['id']) : route('admin.certificates.store') }}" id="certificateForm">
            @csrf
            @if(isset($certificate)) @method('PUT') @endif
            
            <input type="hidden" name="appointment_id" value="{{ $appointment->Appointment_ID ?? $certificate['appointment_id'] }}">
            <input type="hidden" name="service_type" value="{{ $serviceType }}">
            <input type="hidden" name="signature_data" id="signature_data" value="">

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
                        {{-- Pet Name --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Pet Name <span class="text-red-500">*</span></label>
                            <input type="text" name="pet_name" required
                                   value="{{ old('pet_name', $certificate['pet_name'] ?? $appointment->pet->Pet_Name ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all @error('pet_name') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('pet_name')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Animal Type --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Species Type <span class="text-red-500">*</span></label>
                            <select name="animal_type" required class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer @error('animal_type') ring-2 ring-red-400 bg-red-50 @enderror">
                                <option value="">Select Type</option>
                                @php $animalType = old('animal_type', $certificate['animal_type'] ?? ($appointment->pet->species->Species_Name ?? '')); @endphp
                                @foreach(['Dog', 'Cat', 'Bird', 'Rabbit', 'Others'] as $type)
                                    <option value="{{ $type }}" {{ $animalType == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('animal_type')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Pet Gender --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Gender <span class="text-red-500">*</span></label>
                            <select name="pet_gender" required class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer @error('pet_gender') ring-2 ring-red-400 bg-red-50 @enderror">
                                <option value="">Select Gender</option>
                                @php $petGender = old('pet_gender', $certificate['pet_gender'] ?? $appointment->pet->Sex ?? ''); @endphp
                                <option value="Male" {{ $petGender == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $petGender == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('pet_gender')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Pet Age --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Age <span class="text-red-500">*</span></label>
                            <input type="text" name="pet_age" required placeholder="e.g., 2 years, 6 months"
                                   value="{{ old('pet_age', $certificate['pet_age'] ?? $appointment->pet->Age ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all @error('pet_age') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('pet_age')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Pet Breed --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">
                                Breed <span class="text-red-500">*</span>
                                @if($breedMissing) <span class="ml-2 text-[9px] text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md">REQUIRED</span> @endif
                            </label>
                            <input type="text" name="pet_breed" required
                                   placeholder="{{ $breedMissing ? 'Please fill in the breed (e.g., Labrador, Aspin, Mixed)' : '' }}"
                                   value="{{ $petBreed }}"
                                   class="w-full px-5 py-3 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all @error('pet_breed') ring-2 ring-red-400 bg-red-50 @elseif($breedMissing) bg-amber-50 ring-2 ring-amber-200 @else bg-gray-50 @endif">
                            @error('pet_breed')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Pet Color --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">
                                Color <span class="text-red-500">*</span>
                                @if($colorMissing) <span class="ml-2 text-[9px] text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md">REQUIRED</span> @endif
                            </label>
                            <input type="text" name="pet_color" required
                                   placeholder="{{ $colorMissing ? 'Please fill in the color (e.g., Brown, Black & White)' : '' }}"
                                   value="{{ $petColor }}"
                                   class="w-full px-5 py-3 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all @error('pet_color') ring-2 ring-red-400 bg-red-50 @elseif($colorMissing) bg-amber-50 ring-2 ring-amber-200 @else bg-gray-50 @endif">
                            @error('pet_color')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Pet DOB --}}
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Date of Birth</label>
                            <input type="date" name="pet_dob"
                                   value="{{ old('pet_dob', $certificate['pet_dob'] ?? ($appointment->pet->Date_of_Birth ? $appointment->pet->Date_of_Birth->format('Y-m-d') : '')) }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-gray-700 text-sm transition-all @error('pet_dob') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('pet_dob')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
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
                        {{-- Owner Name --}}
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="owner_name" required
                                   value="{{ old('owner_name', $certificate['owner_name'] ?? (($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Middle_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? ''))) }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all @error('owner_name') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('owner_name')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Owner Address --}}
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
                                      class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all resize-none @error('owner_address') ring-2 ring-red-400 bg-red-50 @enderror">{{ old('owner_address', $certificate['owner_address'] ?? $defaultAddress) }}</textarea>
                            @error('owner_address')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Civil Status --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Civil Status <span class="text-red-500">*</span></label>
                            <select name="civil_status" required class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer @error('civil_status') ring-2 ring-red-400 bg-red-50 @enderror">
                                <option value="">Select Status</option>
                                @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $status)
                                    <option value="{{ $status }}" {{ $defaultCivilStatus == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            @error('civil_status')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Years of Residency --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Residency Length <span class="text-red-500">*</span></label>
                            <input type="text" name="years_of_residency" required placeholder="e.g., 5 years"
                                   value="{{ $defaultYearsOfResidency }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all @error('years_of_residency') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('years_of_residency')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Owner Phone --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Contact No. <span class="text-red-500">*</span></label>
                            <input type="text" name="owner_phone" required
                                   value="{{ old('owner_phone', $certificate['owner_phone'] ?? $appointment->user->Contact_Number ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all @error('owner_phone') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('owner_phone')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Owner Birthdate --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Birthdate</label>
                            <input type="date" name="owner_birthdate"
                                   value="{{ $defaultOwnerBirthdate }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-green-500 font-bold text-gray-700 text-sm transition-all @error('owner_birthdate') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('owner_birthdate')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
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
                        {{-- Service Date --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Service Date <span class="text-red-500">*</span></label>
                            <input type="date" name="service_date" required
                                   value="{{ old('service_date', $certificate['service_date'] ?? $certificate['vaccination_date'] ?? (isset($appointment) ? \Carbon\Carbon::parse($appointment->Date)->format('Y-m-d') : '')) }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all @error('service_date') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('service_date')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Next Service Date --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Next Follow-up</label>
                            <input type="date" name="next_service_date"
                                   value="{{ old('next_service_date', $certificate['next_service_date'] ?? $certificate['next_vaccination_date'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all @error('next_service_date') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('next_service_date')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if($isVaccination)
                            {{-- Vaccine Type --}}
                            <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Vaccine Classification <span class="text-red-500">*</span></label>
                                <select name="vaccine_type" id="vaccine_type" required onchange="toggleVaccineFields()"
                                        class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all appearance-none cursor-pointer @error('vaccine_type') ring-2 ring-red-400 bg-red-50 @enderror">
                                    <option value="">Select Category</option>
                                    <option value="anti-rabies" {{ $vaccineType == 'anti-rabies' ? 'selected' : '' }}>Anti-Rabies</option>
                                    <option value="other" {{ $vaccineType == 'other' ? 'selected' : '' }}>Other Vaccine</option>
                                </select>
                                @error('vaccine_type')
                                    <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Anti-Rabies Fields --}}
                            <div id="antiRabiesFields" class="md:col-span-2 {{ $vaccineType != 'anti-rabies' ? 'hidden' : '' }}">
                                <div class="bg-purple-50 p-6 rounded-[1.5rem] border border-purple-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-purple-400 uppercase tracking-widest mb-2 ml-1">Brand Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="vaccine_name_rabies" id="vaccine_name_rabies" 
                                                   value="{{ old('vaccine_name_rabies', ($vaccineType == 'anti-rabies' ? ($vaccineUsed ?: 'Anti-Rabies Vaccine') : 'Anti-Rabies Vaccine')) }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-purple-900 text-sm transition-all @error('vaccine_name_rabies') ring-2 ring-red-400 bg-red-50 @enderror" 
                                                   placeholder="e.g., Anti-Rabies Vaccine, Rabisin, Nobivac Rabies">
                                            @error('vaccine_name_rabies')
                                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-purple-400 uppercase tracking-widest mb-2 ml-1">Lot / Batch No. <span class="text-red-500">*</span></label>
                                            <input type="text" name="lot_number" id="lot_number_rabies" 
                                                   value="{{ old('lot_number', $certificate['lot_number'] ?? '') }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-purple-900 text-sm transition-all font-mono @error('lot_number') ring-2 ring-red-400 bg-red-50 @enderror" 
                                                   placeholder="e.g., LOT-2024-001">
                                            @error('lot_number')
                                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Other Vaccine Fields --}}
                            <div id="otherVaccineFields" class="md:col-span-2 {{ $vaccineType != 'other' ? 'hidden' : '' }}">
                                <div class="bg-yellow-50 p-6 rounded-[1.5rem] border border-yellow-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-2 ml-1">Vaccine Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="vaccine_name_other" id="vaccine_name_other" 
                                                   value="{{ old('vaccine_name_other', ($vaccineType == 'other' ? $vaccineUsed : '')) }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-yellow-500 font-bold text-yellow-900 text-sm transition-all @error('vaccine_name_other') ring-2 ring-red-400 bg-red-50 @enderror" 
                                                   placeholder="Enter vaccine name (e.g., 5-in-1, Parvovirus, etc.)">
                                            @error('vaccine_name_other')
                                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-2 ml-1">Lot / Batch No. <span class="text-red-500">*</span></label>
                                            <input type="text" name="lot_number_other" id="lot_number_other" 
                                                   value="{{ old('lot_number_other', ($vaccineType == 'other' ? ($certificate['lot_number'] ?? '') : '')) }}"
                                                   class="w-full px-5 py-3 bg-white border-none rounded-xl focus:ring-2 focus:ring-yellow-500 font-bold text-yellow-900 text-sm transition-all font-mono @error('lot_number_other') ring-2 ring-red-400 bg-red-50 @enderror" 
                                                   placeholder="e.g., LOT-2024-001">
                                            @error('lot_number_other')
                                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="vaccine_used" id="vaccine_used_final" value="{{ $vaccineUsed }}">
                            <input type="hidden" name="lot_number_final" id="lot_number_final" value="{{ old('lot_number', $certificate['lot_number'] ?? '') }}">

                        @elseif($isDeworming)
                            {{-- Medicine Used --}}
                            <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Medication Used</label>
                                <input type="text" name="medicine_used" 
                                       value="{{ old('medicine_used', $certificate['medicine_used'] ?? $certificate['vaccine_used'] ?? '') }}"
                                       class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all @error('medicine_used') ring-2 ring-red-400 bg-red-50 @enderror" 
                                       placeholder="e.g., Albendazole, Pyrantel Pamoate">
                                @error('medicine_used')
                                    <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Dosage --}}
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Dosage</label>
                                <input type="text" name="dosage" 
                                       value="{{ old('dosage', $certificate['dosage'] ?? '') }}"
                                       class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all @error('dosage') ring-2 ring-red-400 bg-red-50 @enderror" 
                                       placeholder="e.g., 1 tablet, 5ml">
                                @error('dosage')
                                    <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @else
                            {{-- Findings --}}
                            <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Findings / Remarks</label>
                                <textarea name="findings" rows="3" class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all resize-none @error('findings') ring-2 ring-red-400 bg-red-50 @enderror" 
                                          placeholder="Enter checkup findings or remarks...">{{ old('findings', $certificate['findings'] ?? '') }}</textarea>
                                @error('findings')
                                    <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Recommendations --}}
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Recommendations</label>
                                <textarea name="recommendations" rows="2" class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 font-bold text-gray-700 text-sm transition-all resize-none @error('recommendations') ring-2 ring-red-400 bg-red-50 @enderror" 
                                          placeholder="Enter recommendations if any...">{{ old('recommendations', $certificate['recommendations'] ?? '') }}</textarea>
                                @error('recommendations')
                                    <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                    </p>
                                @enderror
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
                        {{-- Veterinarian Name --}}
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Veterinarian Name <span class="text-red-500">*</span></label>
                            <input type="text" name="veterinarian_name" required
                                   value="{{ old('veterinarian_name', $certificate['veterinarian_name'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-500 font-bold text-gray-700 text-sm transition-all @error('veterinarian_name') ring-2 ring-red-400 bg-red-50 @enderror">
                            @error('veterinarian_name')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- License Number --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">License No. <span class="text-red-500">*</span></label>
                            <input type="text" name="license_number" required
                                   value="{{ old('license_number', $certificate['license_number'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-500 font-bold text-gray-700 text-sm transition-all font-mono @error('license_number') ring-2 ring-red-400 bg-red-50 @enderror" 
                                   placeholder="e.g., VET-12345">
                            @error('license_number')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- PTR Number --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">PTR No. <span class="text-red-500">*</span></label>
                            <input type="text" name="ptr_number" required
                                   value="{{ old('ptr_number', $certificate['ptr_number'] ?? '') }}"
                                   class="w-full px-5 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-red-500 font-bold text-gray-700 text-sm transition-all font-mono @error('ptr_number') ring-2 ring-red-400 bg-red-50 @enderror" 
                                   placeholder="e.g., PTR-2024-001">
                            @error('ptr_number')
                                <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- E-Signature Section --}}
                        <div class="md:col-span-2 pt-6 border-t border-gray-100">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 ml-1">
                                <i class="fas fa-pen-fancy mr-2 text-red-500"></i>
                                E-Signature <span class="text-red-500">*</span>
                                <span class="ml-2 text-[9px] text-gray-400 font-medium normal-case">(Draw your signature below)</span>
                            </label>
                            
                            <div class="relative">
                                {{-- Signature Canvas Container --}}
                                <div class="bg-gray-50 rounded-2xl p-4 border-2 border-dashed border-gray-200 hover:border-red-300 transition-colors @error('signature_data') border-red-400 bg-red-50/30 @enderror" id="signatureContainer">
                                    <canvas id="signatureCanvas" class="w-full bg-white rounded-xl cursor-crosshair shadow-inner" style="height: 200px; touch-action: none;"></canvas>
                                    
                                    {{-- Signature Line --}}
                                    <div class="absolute bottom-12 left-8 right-8 border-b-2 border-gray-300 pointer-events-none"></div>
                                    <p class="text-center text-[9px] text-gray-400 uppercase tracking-widest mt-3 font-bold">Sign Above the Line</p>
                                </div>

                                @error('signature_data')
                                    <p class="mt-1.5 ml-1 text-xs font-semibold text-red-600 flex items-center gap-1">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i> {{ $message }}
                                    </p>
                                @enderror

                                {{-- Signature Controls --}}
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center gap-2">
                                        <span id="signatureStatus" class="text-[10px] font-bold uppercase tracking-widest text-amber-600">
                                            <i class="fas fa-exclamation-circle mr-1"></i> No signature
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="undoSignature()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 text-[10px] font-black uppercase tracking-widest transition flex items-center gap-2">
                                            <i class="fas fa-undo"></i> Undo
                                        </button>
                                        <button type="button" onclick="clearSignature()" class="px-4 py-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 text-[10px] font-black uppercase tracking-widest transition flex items-center gap-2">
                                            <i class="fas fa-eraser"></i> Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
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

{{-- E-Signature JavaScript --}}
<script>
    // Signature Pad Implementation
    class SignaturePad {
        constructor(canvas) {
            this.canvas = canvas;
            this.ctx = canvas.getContext('2d');
            this.isDrawing = false;
            this.lastX = 0;
            this.lastY = 0;
            this.strokes = [];
            this.currentStroke = [];
            
            this.init();
        }

        init() {
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());
            
            // Mouse events
            this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
            this.canvas.addEventListener('mousemove', (e) => this.draw(e));
            this.canvas.addEventListener('mouseup', () => this.stopDrawing());
            this.canvas.addEventListener('mouseout', () => this.stopDrawing());
            
            // Touch events
            this.canvas.addEventListener('touchstart', (e) => this.startDrawing(e));
            this.canvas.addEventListener('touchmove', (e) => this.draw(e));
            this.canvas.addEventListener('touchend', () => this.stopDrawing());
            
            // Set drawing style
            this.ctx.strokeStyle = '#1a1a2e';
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        }

        resizeCanvas() {
            const rect = this.canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            
            this.canvas.width = rect.width * dpr;
            this.canvas.height = rect.height * dpr;
            
            this.ctx.scale(dpr, dpr);
            this.canvas.style.width = rect.width + 'px';
            this.canvas.style.height = rect.height + 'px';
            
            // Restore drawing style after resize
            this.ctx.strokeStyle = '#1a1a2e';
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            
            // Redraw all strokes
            this.redraw();
        }

        getCoordinates(e) {
            const rect = this.canvas.getBoundingClientRect();
            let x, y;
            
            if (e.touches && e.touches.length > 0) {
                x = e.touches[0].clientX - rect.left;
                y = e.touches[0].clientY - rect.top;
            } else {
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }
            
            return { x, y };
        }

        startDrawing(e) {
            e.preventDefault();
            this.isDrawing = true;
            const coords = this.getCoordinates(e);
            this.lastX = coords.x;
            this.lastY = coords.y;
            this.currentStroke = [{ x: coords.x, y: coords.y }];
        }

        draw(e) {
            if (!this.isDrawing) return;
            e.preventDefault();
            
            const coords = this.getCoordinates(e);
            
            this.ctx.beginPath();
            this.ctx.moveTo(this.lastX, this.lastY);
            this.ctx.lineTo(coords.x, coords.y);
            this.ctx.stroke();
            
            this.currentStroke.push({ x: coords.x, y: coords.y });
            this.lastX = coords.x;
            this.lastY = coords.y;
        }

        stopDrawing() {
            if (this.isDrawing && this.currentStroke.length > 0) {
                this.strokes.push([...this.currentStroke]);
                this.currentStroke = [];
            }
            this.isDrawing = false;
            this.updateStatus();
        }

        redraw() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            for (const stroke of this.strokes) {
                if (stroke.length < 2) continue;
                
                this.ctx.beginPath();
                this.ctx.moveTo(stroke[0].x, stroke[0].y);
                
                for (let i = 1; i < stroke.length; i++) {
                    this.ctx.lineTo(stroke[i].x, stroke[i].y);
                }
                this.ctx.stroke();
            }
        }

        undo() {
            if (this.strokes.length > 0) {
                this.strokes.pop();
                this.redraw();
                this.updateStatus();
            }
        }

        clear() {
            this.strokes = [];
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.updateStatus();
        }

        isEmpty() {
            return this.strokes.length === 0;
        }

        updateStatus() {
            const statusEl = document.getElementById('signatureStatus');
            if (this.isEmpty()) {
                statusEl.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> No signature';
                statusEl.className = 'text-[10px] font-bold uppercase tracking-widest text-amber-600';
            } else {
                statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Signature captured';
                statusEl.className = 'text-[10px] font-bold uppercase tracking-widest text-green-600';
            }
        }

        toDataURL() {
            if (this.isEmpty()) return '';
            
            // Create a temporary canvas with white background
            const tempCanvas = document.createElement('canvas');
            const rect = this.canvas.getBoundingClientRect();
            tempCanvas.width = rect.width * 2;
            tempCanvas.height = rect.height * 2;
            
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.scale(2, 2);
            
            // Fill with white background
            tempCtx.fillStyle = '#ffffff';
            tempCtx.fillRect(0, 0, rect.width, rect.height);
            
            // Draw the signature
            tempCtx.strokeStyle = '#1a1a2e';
            tempCtx.lineWidth = 2.5;
            tempCtx.lineCap = 'round';
            tempCtx.lineJoin = 'round';
            
            for (const stroke of this.strokes) {
                if (stroke.length < 2) continue;
                
                tempCtx.beginPath();
                tempCtx.moveTo(stroke[0].x, stroke[0].y);
                
                for (let i = 1; i < stroke.length; i++) {
                    tempCtx.lineTo(stroke[i].x, stroke[i].y);
                }
                tempCtx.stroke();
            }
            
            return tempCanvas.toDataURL('image/png');
        }
    }

    // Initialize signature pad
    let signaturePad;
    
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signatureCanvas');
        signaturePad = new SignaturePad(canvas);

        // Auto-scroll to first error field on page load
        @if($errors->any())
            const firstError = document.querySelector('.ring-red-400');
            if (firstError) {
                setTimeout(() => {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }, 300);
            }
        @endif
    });

    function clearSignature() {
        signaturePad.clear();
    }

    function undoSignature() {
        signaturePad.undo();
    }

    // Form submission handling
    document.getElementById('certificateForm').addEventListener('submit', function(e) {
        const action = e.submitter?.value;
        
        // Only require signature for approval
        if (action === 'approve') {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Please provide your signature before approving the certificate.');
                document.getElementById('signatureCanvas').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }
        
        // Set signature data
        document.getElementById('signature_data').value = signaturePad.toDataURL();
        
        // Handle vaccine fields if applicable
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
</script>

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

    document.addEventListener('DOMContentLoaded', function() {
        toggleVaccineFields();
    });
</script>
@endif
@endsection