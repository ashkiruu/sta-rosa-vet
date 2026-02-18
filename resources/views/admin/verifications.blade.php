@extends('layouts.admin')

@section('page_title', 'Resident Management')

@section('content')
{{-- Filter Section --}}
<div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 mb-8">
    <form action="{{ route('admin.verifications') }}" method="GET" class="flex flex-col lg:flex-row gap-6 items-end">
        {{-- Search Input - Blue Accent --}}
        <div class="w-full lg:flex-1">
            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Search Resident</label>
            <div class="relative mt-2">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-blue-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or ID..." 
                    class="w-full bg-blue-50/50 border-none rounded-2xl py-3 pl-10 pr-4 text-sm focus:ring-2 focus:ring-blue-500 transition shadow-inner font-bold text-gray-700">
            </div>
        </div>

        {{-- Dropdown - Red Accent --}}
        <div class="w-full lg:w-64">
            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Verification Status</label>
            <div class="relative mt-2">
                <select name="status" class="w-full bg-red-50/50 border-none rounded-2xl py-3 px-4 text-sm focus:ring-2 focus:ring-red-500 transition shadow-inner appearance-none font-bold text-gray-700 pr-10">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pending</option>
                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Verified</option>
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-red-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-2 w-full lg:w-auto">
            <button type="submit" class="flex-1 lg:flex-none bg-gray-900 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition shadow-md">
                Filter
            </button>
            <a href="{{ route('admin.verifications') }}" class="flex-1 lg:flex-none bg-gray-100 text-gray-500 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition text-center">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Results Summary --}}
<div class="flex items-center justify-between mb-4 px-2">
    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
        Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} residents
    </p>
    @if(request('search') || request('status'))
        <a href="{{ route('admin.verifications') }}" class="text-[10px] font-black text-red-500 uppercase tracking-widest hover:text-red-700 transition">
            <i class="fas fa-times mr-1"></i> Clear Filters
        </a>
    @endif
</div>

{{-- Residents Table --}}
<div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Resident Details</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Account Status</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Join Date</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50/50 transition group">
                    {{-- User Profile --}}
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gray-400 text-white flex items-center justify-center font-black text-xs shadow-md group-hover:bg-red-700 transition-colors">
                                {{ substr($user->First_Name, 0, 1) }}{{ substr($user->Last_Name, 0, 1) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.user.show', $user->User_ID) }}" class="text-sm font-black text-gray-900 hover:text-red-700 transition uppercase tracking-tight">
                                    {{ $user->First_Name }} {{ $user->Last_Name }}
                                </a>
                                <p class="text-[11px] text-gray-500 font-bold tracking-tight lowercase">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Status Badges - High Contrast --}}
                    <td class="px-8 py-5">
                        @if($user->Verification_Status_ID == 2)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-green-100 text-green-700 border border-green-200">
                                <i class="fas fa-check-circle mr-2 text-[10px]"></i> Verified
                            </span>
                        @elseif($user->Verification_Status_ID == 1)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-amber-100 text-amber-700 border border-amber-200">
                                <i class="fas fa-clock mr-2 text-[10px] animate-pulse"></i> Pending Review
                            </span>
                        @else
                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-red-100 text-red-700 border border-red-200">
                                <i class="fas fa-times-circle mr-2 text-[10px]"></i> Rejected
                            </span>
                        @endif
                    </td>

                    {{-- Join Date --}}
                    <td class="px-8 py-5">
                        <span class="text-[11px] font-black text-gray-600 uppercase tracking-tighter">
                            {{ $user->created_at->format('M d, Y') }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-8 py-5">
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('admin.user.show', $user->User_ID) }}" 
                               class="text-[9px] font-black text-gray-500 uppercase tracking-widest px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-800 hover:text-white transition shadow-sm">
                                <i class="fas fa-folder-open mr-1"></i> View File
                            </a>

                            @if($user->Verification_Status_ID == 1)
                                <form action="{{ route('admin.user.approve', $user->User_ID) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[9px] font-black text-white uppercase tracking-widest px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 transition shadow-md shadow-red-100">
                                        Approve
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-24 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-[1.5rem] flex items-center justify-center mb-4">
                                <i class="fas fa-user-slash text-2xl text-gray-200"></i>
                            </div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">No Residents Found matching criteria</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/30">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Page Info --}}
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                </p>

                {{-- Pagination Links --}}
                <div class="flex items-center gap-2">
                    {{-- Previous Button --}}
                    @if($users->onFirstPage())
                        <span class="px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-gray-100 text-gray-300 cursor-not-allowed border border-gray-100">
                            <i class="fas fa-chevron-left mr-1"></i> Prev
                        </span>
                    @else
                        <a href="{{ $users->appends(request()->query())->previousPageUrl() }}" 
                           class="px-4 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                            <i class="fas fa-chevron-left mr-1"></i> Prev
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $users->currentPage();
                        $lastPage = $users->lastPage();
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($startPage > 1)
                        <a href="{{ $users->appends(request()->query())->url(1) }}" 
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
                            <a href="{{ $users->appends(request()->query())->url($page) }}" 
                               class="w-10 h-10 rounded-xl text-[10px] font-black flex items-center justify-center bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    @if($endPage < $lastPage)
                        @if($endPage < $lastPage - 1)
                            <span class="w-10 h-10 flex items-center justify-center text-gray-300 text-xs font-black">…</span>
                        @endif
                        <a href="{{ $users->appends(request()->query())->url($lastPage) }}" 
                           class="w-10 h-10 rounded-xl text-[10px] font-black flex items-center justify-center bg-white text-gray-600 hover:bg-gray-900 hover:text-white transition shadow-sm border border-gray-200">
                            {{ $lastPage }}
                        </a>
                    @endif

                    {{-- Next Button --}}
                    @if($users->hasMorePages())
                        <a href="{{ $users->appends(request()->query())->nextPageUrl() }}" 
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
@endsection