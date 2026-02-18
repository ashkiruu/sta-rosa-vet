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
                <p class="text-xl font-black text-gray-900 leading-none mt-1">{{ $totalCertificates }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-amber-600 group-hover:text-white transition-all">
                <i class="fas fa-pen-nib text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Drafts</p>
                <p class="text-xl font-black text-amber-600 leading-none mt-1">{{ $draftCount }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-green-600 group-hover:text-white transition-all">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Approved</p>
                <p class="text-xl font-black text-green-600 leading-none mt-1">{{ $approvedCount }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-sm group-hover:bg-purple-600 group-hover:text-white transition-all">
                <i class="fas fa-hospital-user text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pending Generation</p>
                <p class="text-xl font-black text-purple-600 leading-none mt-1">{{ $pendingGenerationCount }}</p>
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
                
                <div class="divide-y divide-gray-50 overflow-y-auto flex-grow custom-scrollbar">
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

                {{-- Completed Appointments Pagination --}}
                @if($completedAppointments->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                        <div class="flex items-center justify-between">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                {{ $completedAppointments->firstItem() }}–{{ $completedAppointments->lastItem() }} of {{ $completedAppointments->total() }}
                            </p>
                            <div class="flex items-center gap-1">
                                @if($completedAppointments->onFirstPage())
                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 bg-gray-100 cursor-not-allowed">
                                        <i class="fas fa-chevron-left text-[8px]"></i>
                                    </span>
                                @else
                                    <a href="{{ $completedAppointments->appends(request()->query())->previousPageUrl() }}" 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-600 bg-white border border-gray-200 hover:bg-gray-900 hover:text-white transition shadow-sm">
                                        <i class="fas fa-chevron-left text-[8px]"></i>
                                    </a>
                                @endif

                                @foreach($completedAppointments->getUrlRange(max(1, $completedAppointments->currentPage() - 1), min($completedAppointments->lastPage(), $completedAppointments->currentPage() + 1)) as $page => $url)
                                    @if($page == $completedAppointments->currentPage())
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-black bg-purple-700 text-white shadow-sm">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $completedAppointments->appends(request()->query())->url($page) }}" 
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-black bg-white text-gray-600 border border-gray-200 hover:bg-gray-900 hover:text-white transition shadow-sm">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach

                                @if($completedAppointments->hasMorePages())
                                    <a href="{{ $completedAppointments->appends(request()->query())->nextPageUrl() }}" 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-600 bg-white border border-gray-200 hover:bg-gray-900 hover:text-white transition shadow-sm">
                                        <i class="fas fa-chevron-right text-[8px]"></i>
                                    </a>
                                @else
                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 bg-gray-100 cursor-not-allowed">
                                        <i class="fas fa-chevron-right text-[8px]"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Certificates Management Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="bg-gray-900 px-8 py-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">All Issued Certificates</h2>
                            <p class="text-[10px] text-gray-400 uppercase font-bold mt-1">Manage, Approve, and Print Records</p>
                        </div>
                        @if($certificates->total() > 0)
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-800 px-3 py-1.5 rounded-lg">
                                {{ $certificates->total() }} {{ request('cert_status') ? request('cert_status') : 'total' }}
                            </span>
                        @endif
                    </div>

                    {{-- Filter Tabs --}}
                    <div class="flex items-center gap-2 mt-5">
                        <a href="{{ route('admin.certificates.index', request()->except(['cert_status', 'page'])) }}" 
                           class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition
                                  {{ !request('cert_status') ? 'bg-white text-gray-900 shadow-sm' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                            All
                        </a>
                        <a href="{{ route('admin.certificates.index', array_merge(request()->except(['cert_status', 'page']), ['cert_status' => 'draft'])) }}" 
                           class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition
                                  {{ request('cert_status') === 'draft' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request('cert_status') === 'draft' ? 'bg-amber-200' : 'bg-amber-500' }} inline-block mr-1.5 align-middle"></span>
                            Drafts
                            @if($draftCount > 0)
                                <span class="ml-1.5 {{ request('cert_status') === 'draft' ? 'bg-amber-400/50 text-white' : 'bg-gray-700 text-gray-300' }} px-1.5 py-0.5 rounded-md text-[8px]">{{ $draftCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.certificates.index', array_merge(request()->except(['cert_status', 'page']), ['cert_status' => 'approved'])) }}" 
                           class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition
                                  {{ request('cert_status') === 'approved' ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request('cert_status') === 'approved' ? 'bg-green-200' : 'bg-green-500' }} inline-block mr-1.5 align-middle"></span>
                            Approved
                            @if($approvedCount > 0)
                                <span class="ml-1.5 {{ request('cert_status') === 'approved' ? 'bg-green-500/50 text-white' : 'bg-gray-700 text-gray-300' }} px-1.5 py-0.5 rounded-md text-[8px]">{{ $approvedCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
                
                <div class="overflow-x-auto flex-grow">
                    @if($certificates->count() > 0)
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
                                @foreach($certificates as $cert)
                                    <tr class="hover:bg-gray-50/50 transition group">
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <span class="font-mono text-[11px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">
                                                {{ $cert->Certificate_Number }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-gray-800 uppercase tracking-tight">{{ $cert->Pet_Name }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $cert->Owner_Name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="text-[9px] font-black uppercase tracking-tighter text-gray-600">
                                                {{ $cert->Service_Type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @if($cert->Status === 'draft')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-100 text-amber-700">
                                                    <span class="w-1 h-1 rounded-full bg-amber-600 mr-2 animate-pulse"></span> Draft
                                                </span>
                                            @elseif($cert->Status === 'approved')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-green-100 text-green-700">
                                                    <span class="w-1 h-1 rounded-full bg-green-600 mr-2"></span> Approved
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex justify-center gap-2">
                                                @if($cert->Status === 'draft')
                                                    <a href="{{ route('admin.certificates.edit', $cert->Certificate_ID) }}" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </a>
                                                    <form action="{{ route('admin.certificates.approve', $cert->Certificate_ID) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="p-2 text-green-500 hover:bg-green-50 rounded-lg transition" title="Approve">
                                                            <i class="fas fa-check-circle text-xs"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('admin.certificates.view', $cert->Certificate_ID) }}" target="_blank" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Print/View">
                                                        <i class="fas fa-print text-xs"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('admin.certificates.delete', $cert->Certificate_ID) }}" method="POST" class="inline" onsubmit="return confirm('Delete this certificate?');">
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
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2 italic">
                                @if(request('cert_status'))
                                    No {{ request('cert_status') }} certificates found
                                @else
                                    Waiting for approved generations...
                                @endif
                            </p>
                            @if(request('cert_status'))
                                <a href="{{ route('admin.certificates.index') }}" class="inline-block mt-4 text-[9px] font-black text-gray-500 uppercase tracking-widest px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 transition">
                                    <i class="fas fa-times mr-1"></i> Clear Filter
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Certificates Pagination --}}
                @if($certificates->hasPages())
                    <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/30">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                Page {{ $certificates->currentPage() }} of {{ $certificates->lastPage() }}
                                <span class="text-gray-300 mx-2">|</span>
                                Showing {{ $certificates->firstItem() }}–{{ $certificates->lastItem() }} of {{ $certificates->total() }}
                            </p>

                            <div class="flex items-center gap-2">
                                {{-- Previous --}}
                                @if($certificates->onFirstPage())
                                    <span class="px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-gray-100 text-gray-300 cursor-not-allowed border border-gray-100">
                                        <i class="fas fa-chevron-left mr-1"></i> Prev
                                    </span>
                                @else
                                    <a href="{{ $certificates->appends(request()->query())->previousPageUrl() }}" 
                                       class="px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                                        <i class="fas fa-chevron-left mr-1"></i> Prev
                                    </a>
                                @endif

                                {{-- Page Numbers --}}
                                @php
                                    $currentPage = $certificates->currentPage();
                                    $lastPage = $certificates->lastPage();
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                @endphp

                                @if($startPage > 1)
                                    <a href="{{ $certificates->appends(request()->query())->url(1) }}" 
                                       class="w-10 h-10 rounded-xl text-[10px] font-black flex items-center justify-center bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                                        1
                                    </a>
                                    @if($startPage > 2)
                                        <span class="w-10 h-10 flex items-center justify-center text-gray-300 text-xs font-black">…</span>
                                    @endif
                                @endif

                                @for($page = $startPage; $page <= $endPage; $page++)
                                    @if($page == $currentPage)
                                        <span class="w-10 h-10 rounded-xl text-[10px] font-black flex items-center justify-center bg-gray-900 text-white shadow-md">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $certificates->appends(request()->query())->url($page) }}" 
                                           class="w-10 h-10 rounded-xl text-[10px] font-black flex items-center justify-center bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endfor

                                @if($endPage < $lastPage)
                                    @if($endPage < $lastPage - 1)
                                        <span class="w-10 h-10 flex items-center justify-center text-gray-300 text-xs font-black">…</span>
                                    @endif
                                    <a href="{{ $certificates->appends(request()->query())->url($lastPage) }}" 
                                       class="w-10 h-10 rounded-xl text-[10px] font-black flex items-center justify-center bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                                        {{ $lastPage }}
                                    </a>
                                @endif

                                {{-- Next --}}
                                @if($certificates->hasMorePages())
                                    <a href="{{ $certificates->appends(request()->query())->nextPageUrl() }}" 
                                       class="px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                                        Next <i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                @else
                                    <span class="px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-gray-100 text-gray-300 cursor-not-allowed border border-gray-100">
                                        Next <i class="fas fa-chevron-right ml-1"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection