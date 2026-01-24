@extends('layouts.admin')

@section('page_title', 'Certificate Generation')

@section('content')
<div class="min-h-screen py-4">
    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span class="text-xs font-black uppercase tracking-widest">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-exclamation-triangle mr-3"></i>
            <span class="text-xs font-black uppercase tracking-widest">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all">
                <i class="fas fa-file-alt text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Issued</p>
                <p class="text-xl font-black text-gray-900 leading-none mt-1">{{ count($allCertificates) }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-amber-600 group-hover:text-white transition-all">
                <i class="fas fa-pen-nib text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Drafts</p>
                <p class="text-xl font-black text-amber-600 leading-none mt-1">{{ count($draftCertificates) }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-green-600 group-hover:text-white transition-all">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Approved</p>
                <p class="text-xl font-black text-green-600 leading-none mt-1">{{ count($approvedCertificates) }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-purple-600 group-hover:text-white transition-all">
                <i class="fas fa-hospital-user text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pending Generation</p>
                <p class="text-xl font-black text-purple-600 leading-none mt-1">{{ count($completedAppointments) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Completed Appointments Column --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="bg-purple-700 px-8 py-6">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Ready for Generation</h2>
                    <p class="text-[10px] text-purple-200 uppercase font-bold mt-1">Completed Appointments</p>
                </div>
                
                <div class="divide-y divide-gray-50 overflow-y-auto max-h-[600px] flex-grow custom-scrollbar">
                    @forelse($completedAppointments as $appointment)
                        @php
                            $hasCertificate = \App\Services\CertificateService::getCertificateByAppointment($appointment->Appointment_ID);
                        @endphp
                        <div class="p-6 hover:bg-gray-50/80 transition group {{ $hasCertificate ? 'bg-green-50/30' : '' }}">
                            <div class="flex justify-between items-center gap-4">
                                <div class="flex-1">
                                    <p class="text-xs font-black text-gray-900 uppercase tracking-tight">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5 tracking-tighter">{{ $appointment->user->First_Name ?? '' }} {{ $appointment->user->Last_Name ?? '' }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-[9px] font-black text-purple-600 uppercase bg-purple-50 px-2 py-0.5 rounded-md">{{ $appointment->service->Service_Name ?? 'N/A' }}</span>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($appointment->Date)->format('M d, Y') }}</span>
                                    </div>
                                </div>
                                <div>
                                    @if($hasCertificate)
                                        <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center shadow-sm">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.certificates.create', $appointment->Appointment_ID) }}" 
                                           class="bg-gray-900 text-white p-3 rounded-xl hover:bg-purple-600 transition shadow-sm block">
                                            <i class="fas fa-plus text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <i class="fas fa-clipboard-check text-gray-100 text-4xl mb-4"></i>
                            <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No pending tasks</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Certificates Management Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="bg-gray-900 px-8 py-6">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">All Issued Certificates</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mt-1">Manage, Approve, and Print Records</p>
                </div>
                
                <div class="overflow-x-auto flex-grow">
                    @if(count($allCertificates) > 0)
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cert #</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Subject</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($allCertificates as $cert)
                                    <tr class="hover:bg-gray-50/50 transition group">
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <span class="font-mono text-[11px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">
                                                {{ $cert['certificate_number'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-gray-800 uppercase tracking-tight">{{ $cert['pet_name'] }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $cert['owner_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="text-[9px] font-black uppercase tracking-tighter text-gray-600">
                                                {{ $cert['service_type'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @if($cert['status'] === 'draft')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-100 text-amber-700">
                                                    <span class="w-1 h-1 rounded-full bg-amber-600 mr-2 animate-pulse"></span> Draft
                                                </span>
                                            @elseif($cert['status'] === 'approved')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-green-100 text-green-700">
                                                    <span class="w-1 h-1 rounded-full bg-green-600 mr-2"></span> Approved
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex justify-center gap-2">
                                                @if($cert['status'] === 'draft')
                                                    <a href="{{ route('admin.certificates.edit', $cert['id']) }}" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('admin.certificates.approve', $cert['id']) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="p-2 text-green-500 hover:bg-green-50 rounded-lg transition" title="Approve">
                                                            <i class="fas fa-check-circle text-xs"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('admin.certificates.view', $cert['id']) }}" target="_blank" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Print/View">
                                                        <i class="fas fa-print text-xs"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('admin.certificates.delete', $cert['id']) }}" method="POST" class="inline" onsubmit="return confirm('Delete this certificate?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition" title="Delete">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-24 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-4 mx-auto border border-gray-100">
                                <i class="fas fa-scroll text-3xl text-gray-200"></i>
                            </div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">No Certificates Found</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2 italic">Waiting for approved generations...</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection