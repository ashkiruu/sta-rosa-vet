@extends('layouts.admin')

@section('page_title', isset($certificate) ? 'Edit Certificate' : 'Generate Certificate')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ isset($certificate) ? 'Edit Certificate' : 'Generate Certificate' }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ isset($certificate) ? 'Update certificate details' : 'Fill in the details for the vaccination/treatment certificate' }}
                </p>
            </div>
            <a href="{{ route('admin.certificates.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Back to Certificates
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($certificate) ? route('admin.certificates.update', $certificate['id']) : route('admin.certificates.store') }}">
            @csrf
            @if(isset($certificate))
                @method('PUT')
            @endif
            
            <input type="hidden" name="appointment_id" value="{{ $appointment->Appointment_ID ?? $certificate['appointment_id'] }}">

            {{-- Pet Information --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h2 class="text-lg font-bold">🐾 Pet Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pet's Name *</label>
                            <input type="text" name="pet_name" required
                                   value="{{ old('pet_name', $certificate['pet_name'] ?? $appointment->pet->Pet_Name ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type of Animal *</label>
                            <select name="animal_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                @php
                                    $animalType = old('animal_type', $certificate['animal_type'] ?? ($appointment->pet->species->Species_Name ?? ''));
                                @endphp
                                <option value="Dog" {{ $animalType == 'Dog' ? 'selected' : '' }}>Dog</option>
                                <option value="Cat" {{ $animalType == 'Cat' ? 'selected' : '' }}>Cat</option>
                                <option value="Bird" {{ $animalType == 'Bird' ? 'selected' : '' }}>Bird</option>
                                <option value="Rabbit" {{ $animalType == 'Rabbit' ? 'selected' : '' }}>Rabbit</option>
                                <option value="Others" {{ $animalType == 'Others' ? 'selected' : '' }}>Others</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender/Sex *</label>
                            <select name="pet_gender" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Gender</option>
                                @php
                                    $petGender = old('pet_gender', $certificate['pet_gender'] ?? $appointment->pet->Sex ?? '');
                                @endphp
                                <option value="Male" {{ $petGender == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $petGender == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Age *</label>
                            <input type="text" name="pet_age" required placeholder="e.g., 2 years, 6 months"
                                   value="{{ old('pet_age', $certificate['pet_age'] ?? $appointment->pet->Age ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Breed *</label>
                            <input type="text" name="pet_breed" required
                                   value="{{ old('pet_breed', $certificate['pet_breed'] ?? $appointment->pet->Breed ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
                            <input type="text" name="pet_color" required
                                   value="{{ old('pet_color', $certificate['pet_color'] ?? $appointment->pet->Color ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" name="pet_dob"
                                   value="{{ old('pet_dob', $certificate['pet_dob'] ?? ($appointment->pet->Date_of_Birth ? $appointment->pet->Date_of_Birth->format('Y-m-d') : '')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Owner Information --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h2 class="text-lg font-bold">👤 Owner Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Owner's Name *</label>
                            <input type="text" name="owner_name" required
                                   value="{{ old('owner_name', $certificate['owner_name'] ?? (($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Middle_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? ''))) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Complete Address *</label>
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
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('owner_address', $certificate['owner_address'] ?? $defaultAddress) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Civil Status *</label>
                            <select name="civil_status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="">Select Civil Status</option>
                                @php
                                    $civilStatus = old('civil_status', $certificate['civil_status'] ?? '');
                                @endphp
                                <option value="Single" {{ $civilStatus == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ $civilStatus == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Widowed" {{ $civilStatus == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                <option value="Separated" {{ $civilStatus == 'Separated' ? 'selected' : '' }}>Separated</option>
                                <option value="Divorced" {{ $civilStatus == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Years/Length of Residency *</label>
                            <input type="text" name="years_of_residency" required placeholder="e.g., 5 years, Since birth"
                                   value="{{ old('years_of_residency', $certificate['years_of_residency'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cellphone/Telephone Number *</label>
                            <input type="text" name="owner_phone" required
                                   value="{{ old('owner_phone', $certificate['owner_phone'] ?? $appointment->user->Contact_Number ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                            <input type="date" name="owner_birthdate"
                                   value="{{ old('owner_birthdate', $certificate['owner_birthdate'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vaccination/Service Details --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-purple-600 text-white px-6 py-4">
                    <h2 class="text-lg font-bold">💉 Vaccination/Service Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Type *</label>
                            <input type="text" name="service_type" required
                                   value="{{ old('service_type', $certificate['service_type'] ?? $appointment->service->Service_Name ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Vaccination *</label>
                            <input type="date" name="vaccination_date" required
                                   value="{{ old('vaccination_date', $certificate['vaccination_date'] ?? (isset($appointment) ? \Carbon\Carbon::parse($appointment->Date)->format('Y-m-d') : '')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Next Vaccination</label>
                            <input type="date" name="next_vaccination_date"
                                   value="{{ old('next_vaccination_date', $certificate['next_vaccination_date'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vaccine Used *</label>
                            <input type="text" name="vaccine_used" required placeholder="e.g., Rabies Vaccine"
                                   value="{{ old('vaccine_used', $certificate['vaccine_used'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lot Number / Batch Number *</label>
                            <input type="text" name="lot_number" required placeholder="e.g., LOT-2024-001"
                                   value="{{ old('lot_number', $certificate['lot_number'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Veterinarian Details --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
                <div class="bg-red-600 text-white px-6 py-4">
                    <h2 class="text-lg font-bold">👨‍⚕️ Veterinarian Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Veterinarian Name *</label>
                            <input type="text" name="veterinarian_name" required
                                   value="{{ old('veterinarian_name', $certificate['veterinarian_name'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License No. *</label>
                            <input type="text" name="license_number" required placeholder="e.g., VET-12345"
                                   value="{{ old('license_number', $certificate['license_number'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PTR No. *</label>
                            <input type="text" name="ptr_number" required placeholder="e.g., PTR-2024-001"
                                   value="{{ old('ptr_number', $certificate['ptr_number'] ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.certificates.index') }}" 
                   class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </a>
                <button type="submit" name="action" value="draft"
                        class="px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                    💾 Save as Draft
                </button>
                <button type="submit" name="action" value="approve"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    ✅ Save & Approve
                </button>
            </div>
        </form>
    </div>
</div>
@endsection