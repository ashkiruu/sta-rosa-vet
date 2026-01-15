@extends('layouts.admin')

@section('page_title', 'Certificate Generation')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Certificate Generation</h1>
                <p class="text-sm text-gray-600">Generate and manage vaccination/treatment certificates</p>
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

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-2xl">
                        📋
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Certificates</p>
                        <p class="text-2xl font-bold text-gray-800">{{ count($allCertificates) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-2xl">
                        📝
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Draft</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ count($draftCertificates) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-2xl">
                        ✅
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Approved</p>
                        <p class="text-2xl font-bold text-green-600">{{ count($approvedCertificates) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-2xl">
                        🏥
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Completed Appointments</p>
                        <p class="text-2xl font-bold text-purple-600">{{ count($completedAppointments) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Completed Appointments (Ready for Certificate) --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-purple-600 text-white px-6 py-4">
                        <h2 class="text-lg font-bold">Completed Appointments</h2>
                        <p class="text-purple-200 text-sm">Ready for certificate generation</p>
                    </div>
                    
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        @forelse($completedAppointments as $appointment)
                            @php
                                $hasCertificate = \App\Services\CertificateService::getCertificateByAppointment($appointment->Appointment_ID);
                            @endphp
                            <div class="p-4 hover:bg-gray-50 {{ $hasCertificate ? 'bg-green-50' : '' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-800">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">{{ $appointment->user->First_Name ?? '' }} {{ $appointment->user->Last_Name ?? '' }}</p>
                                        <p class="text-xs text-gray-400">{{ $appointment->service->Service_Name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($appointment->Date)->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        @if($hasCertificate)
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                ✓ Certificate
                                            </span>
                                        @else
                                            <a href="{{ route('admin.certificates.create', $appointment->Appointment_ID) }}" 
                                               class="px-3 py-1 text-xs bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                                + Generate
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-500">
                                <p>No completed appointments</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Certificates List --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-800 text-white px-6 py-4">
                        <h2 class="text-lg font-bold">All Certificates</h2>
                        <p class="text-gray-300 text-sm">Manage generated certificates</p>
                    </div>
                    
                    @if(count($allCertificates) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate #</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pet</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($allCertificates as $cert)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="font-mono text-sm font-semibold text-blue-600">
                                                    {{ $cert['certificate_number'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-sm text-gray-900">{{ $cert['pet_name'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-sm text-gray-500">{{ $cert['owner_name'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                    {{ $cert['service_type'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @if($cert['status'] === 'draft')
                                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                                        📝 Draft
                                                    </span>
                                                @elseif($cert['status'] === 'approved')
                                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                        ✅ Approved
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex gap-2">
                                                    @if($cert['status'] === 'draft')
                                                        <a href="{{ route('admin.certificates.edit', $cert['id']) }}" 
                                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                                            Edit
                                                        </a>
                                                        <form action="{{ route('admin.certificates.approve', $cert['id']) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-green-600 hover:text-green-800 text-sm">
                                                                Approve
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route('admin.certificates.view', $cert['id']) }}" 
                                                           target="_blank"
                                                           class="text-green-600 hover:text-green-800 text-sm">
                                                            View/Print
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('admin.certificates.delete', $cert['id']) }}" method="POST" class="inline" 
                                                          onsubmit="return confirm('Delete this certificate?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="text-5xl mb-4">📜</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Certificates Yet</h3>
                            <p class="text-gray-500">Generate certificates from completed appointments</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection