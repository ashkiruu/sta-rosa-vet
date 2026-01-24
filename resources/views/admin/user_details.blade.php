@extends('layouts.admin')

@section('title', 'Verification Hub')

@section('content')
<div class="min-h-screen py-6 bg-[#f8fafc]">
    {{-- Top Navigation Bar --}}
    <div class="max-w-[1400px] mx-auto px-4 mb-6 flex items-center">
        <a href="{{ route('admin.verifications') }}" class="group inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-red-600 transition-colors">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Queue
        </a>
    </div>

    <div class="max-w-[1400px] mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- MAIN PANEL --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- Profile Header Card --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-8">
                            {{-- Profile Picture (Simplified & Straight) --}}
                            <div class="w-32 h-32 bg-gray-900 rounded-3xl flex items-center justify-center text-3xl font-black text-white shadow-lg border-4 border-white shrink-0">
                                {{ strtoupper(substr($user->First_Name, 0, 1)) }}{{ strtoupper(substr($user->Last_Name, 0, 1)) }}
                            </div>

                            <div class="flex-1 text-center md:text-left">
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-2">
                                    <h3 class="text-3xl font-black text-gray-900 tracking-tight uppercase">{{ $user->First_Name }} {{ $user->Last_Name }}</h3>
                                    {{-- User ID moved inside Profile Box --}}
                                    <span class="px-3 py-1 bg-gray-100 text-gray-500 text-[10px] font-black rounded-lg tracking-widest border border-gray-200">ID: #{{ $user->User_ID }}</span>
                                </div>
                                
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">
                                    <i class="far fa-envelope mr-2 text-red-500"></i>{{ $user->Email }}
                                </p>

                                <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                    @if($user->Verification_Status_ID == 1)
                                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-widest">⏳ Action Required</span>
                                    @elseif($user->Verification_Status_ID == 2)
                                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black bg-green-50 text-green-600 border border-green-100 uppercase tracking-widest">✓ Verified Resident</span>
                                    @elseif($user->Verification_Status_ID == 3)
                                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black bg-red-50 text-red-600 border border-red-100 uppercase tracking-widest">✕ Request Denied</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        {{-- Data Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-gray-50 pt-8">
                            <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Contact</p>
                                <p class="text-xs font-black text-gray-700">{{ $user->Contact_Number ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Birth Date</p>
                                <p class="text-xs font-black text-gray-700">{{ $user->Date_of_Birth ? \Carbon\Carbon::parse($user->Date_of_Birth)->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Registered</p>
                                <p class="text-xs font-black text-gray-700">{{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Address Banner --}}
                    <div class="px-8 pb-8">
                        <div class="bg-red-50/50 rounded-2xl p-5 border border-red-100/50">
                            <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-1">Residential Address (Self-Reported)</p>
                            <p class="text-xs font-bold text-gray-700 italic">"{{ $user->Address ?? 'No address provided' }}"</p>
                        </div>
                    </div>
                </div>

                {{-- OCR ANALYSIS PANEL --}}
                @if($user->ocrData)
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-900 px-8 py-4 flex items-center justify-between">
                            <h4 class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Automated ID Analysis</h4>
                        </div>
                        
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                {{-- Address Match --}}
                                <div class="p-6 rounded-2xl border-2 {{ $user->ocrData->Address_Match_Status === 'Matched' ? 'border-green-100 bg-green-50' : 'border-red-100 bg-red-50' }}">
                                    <p class="text-[9px] font-black uppercase tracking-widest mb-2 {{ $user->ocrData->Address_Match_Status === 'Matched' ? 'text-green-600' : 'text-red-600' }}">Address Verification</p>
                                    <div class="flex items-center gap-3">
                                        <i class="fas {{ $user->ocrData->Address_Match_Status === 'Matched' ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500' }} text-xl"></i>
                                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight">{{ $user->ocrData->Address_Match_Status }}</span>
                                    </div>
                                </div>
                                
                                {{-- Confidence Score (Replaced ID Name Extraction) --}}
                                <div class="p-6 bg-purple-50 border-2 border-purple-100 rounded-2xl">
                                    <p class="text-[9px] font-black text-purple-600 uppercase tracking-widest mb-2">OCR Confidence Score</p>
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-brain text-purple-400 text-xl"></i>
                                        <span class="text-lg font-black text-gray-900 uppercase tracking-tight">{{ number_format($user->ocrData->Confidence_Score * 100, 2) }}%</span>
                                    </div>
                                </div>
                            </div>

                            {{-- ID Image View --}}
                            @if($user->ocrData->Document_Image_Path)
                                <div class="bg-gray-50 rounded-[1.5rem] p-4 border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center mb-4">Original Document Scan</p>
                                    <img src="{{ Storage::url($user->ocrData->Document_Image_Path) }}" class="w-full rounded-xl shadow-md border border-white">
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase italic">No uploaded ID image found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- DECISION ACTION BAR --}}
                @if($user->Verification_Status_ID == 1)
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <form action="{{ route('admin.user.approve', $user->User_ID) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-black text-[10px] uppercase tracking-[0.2em] py-5 rounded-2xl transition-all shadow-lg shadow-green-200">
                                ✓ Approve Resident
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.user.reject', $user->User_ID) }}" method="POST" class="flex-1" 
                              onsubmit="return confirm('Reject this application?');">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black text-[10px] uppercase tracking-[0.2em] py-5 rounded-2xl transition-all shadow-lg shadow-red-200">
                                ✕ Reject Application
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- SIDEBAR --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                    <div class="bg-amber-400 px-8 py-5 flex justify-between items-center">
                        <h2 class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Registered Pets</h2>
                        <span class="bg-amber-500/50 px-3 py-1 rounded-lg text-[10px] font-black text-white">{{ $pets->count() }}</span>
                    </div>
                    
                    <div class="p-6">
                        @forelse($pets as $pet)
                            <div class="mb-4 last:mb-0">
                                <div class="bg-gray-50/50 rounded-2xl p-5 border border-gray-100 hover:border-amber-200 transition-all">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-xl border border-gray-100">
                                            @if($pet->Species_ID == 1) 🐕 @elseif($pet->Species_ID == 2) 🐈 @else 🐾 @endif
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-gray-900 uppercase tracking-tight">{{ $pet->Pet_Name }}</h4>
                                            <p class="text-[8px] font-black text-amber-500 uppercase tracking-widest">{{ $pet->species->Species_Name ?? 'UNKNOWN' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-3">
                                        <span class="text-[9px] font-bold text-gray-500 uppercase"><span class="text-gray-300 mr-1">Sex:</span> {{ $pet->Sex }}</span>
                                        <span class="text-[9px] font-bold text-gray-500 uppercase"><span class="text-gray-300 mr-1">Age:</span> {{ $pet->Age }}mo</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">No Registered Pets</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection