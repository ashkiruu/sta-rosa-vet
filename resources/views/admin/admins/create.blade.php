@extends('layouts.admin')

@section('page_title', 'Create Admin Account')

@section('content')
<div class="min-h-screen py-4">
    {{-- Back Navigation --}}
    <div class="mb-8">
        <a href="{{ route('admin.admins.index') }}" class="group inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-purple-600 transition-colors">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Return to Directory
        </a>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            {{-- Form Header --}}
            <div class="bg-gray-900 px-10 py-8 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black text-white uppercase tracking-tight">Privilege Escalation</h2>
                    <p class="text-[10px] font-bold text-purple-400 uppercase tracking-[0.2em] mt-1">Grant administrative access to verified accounts</p>
                </div>
                <div class="h-12 w-12 bg-purple-600/20 rounded-2xl flex items-center justify-center border border-purple-500/30">
                    <i class="fas fa-shield-alt text-purple-400"></i>
                </div>
            </div>

            <div class="p-10">
                @if($eligibleUsers->isEmpty())
                    <div class="bg-amber-50 border border-amber-100 rounded-[2rem] p-10 text-center">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-slash text-amber-500 text-xl"></i>
                        </div>
                        <h4 class="text-xs font-black text-amber-900 uppercase tracking-widest">No Candidates Available</h4>
                        <p class="text-[10px] font-bold text-amber-700 uppercase mt-2 leading-relaxed italic">
                            All verified users currently hold admin status, <br>or no verified accounts exist in the registry.
                        </p>
                    </div>
                @else
                    <form action="{{ route('admin.admins.store') }}" method="POST" class="space-y-8">
                        @csrf
                        
                        {{-- User Selection --}}
                        <div>
                            <label for="user_id" class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">
                                1. Target Account <span class="text-purple-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="user_id" id="user_id" required 
                                    class="w-full appearance-none bg-gray-50 border-none rounded-2xl px-6 py-4 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-purple-500 transition-all cursor-pointer">
                                    <option value="">-- Select from verified personnel --</option>
                                    @foreach($eligibleUsers as $user)
                                        <option value="{{ $user->User_ID }}" {{ old('user_id') == $user->User_ID ? 'selected' : '' }}>
                                            {{ $user->First_Name }} {{ $user->Last_Name }} — {{ $user->Email }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-6 pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            @error('user_id')
                                <p class="text-red-500 text-[9px] font-black uppercase mt-2 ml-1 tracking-widest">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Role Selection --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">
                                2. Define Authority Level <span class="text-purple-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Staff Role --}}
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="admin_role" value="staff" {{ old('admin_role', 'staff') === 'staff' ? 'checked' : '' }} class="peer hidden">
                                    <div class="h-full p-6 border-2 border-gray-50 bg-gray-50 rounded-[1.5rem] transition-all peer-checked:border-purple-600 peer-checked:bg-white peer-checked:shadow-md group-hover:bg-white">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <i class="fas fa-user text-xs"></i>
                                            </div>
                                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Staff Member</span>
                                        </div>
                                        <p class="text-[9px] font-bold text-gray-400 leading-relaxed uppercase">Operation-centric access. verification, appointment logs, and reporting. full activity auditing enabled.</p>
                                    </div>
                                    <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-purple-600 text-lg"></i>
                                    </div>
                                </label>

                                {{-- Admin Role --}}
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="admin_role" value="admin" {{ old('admin_role') === 'admin' ? 'checked' : '' }} class="peer hidden">
                                    <div class="h-full p-6 border-2 border-gray-50 bg-gray-50 rounded-[1.5rem] transition-all peer-checked:border-purple-600 peer-checked:bg-white peer-checked:shadow-md group-hover:bg-white">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <i class="fas fa-user-tie text-xs"></i>
                                            </div>
                                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Administrator</span>
                                        </div>
                                        <p class="text-[9px] font-bold text-gray-400 leading-relaxed uppercase">Management-centric access. inherited staff permissions with future oversight and system configuration modules.</p>
                                    </div>
                                    <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-purple-600 text-lg"></i>
                                    </div>
                                </label>
                            </div>
                            @error('admin_role')
                                <p class="text-red-500 text-[9px] font-black uppercase mt-2 ml-1 tracking-widest">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Security Policy --}}
                        <div class="bg-purple-50 rounded-2xl p-6 border border-purple-100 relative overflow-hidden">
                            <i class="fas fa-fingerprint absolute -right-2 -bottom-2 text-6xl text-purple-600/5"></i>
                            <h4 class="text-[9px] font-black text-purple-900 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fas fa-user-shield"></i> Security Protocol Awareness
                            </h4>
                            <ul class="space-y-2">
                                <li class="flex items-start gap-2 text-[9px] font-bold text-purple-700/80 uppercase">
                                    <span class="text-purple-400">•</span> Account inherits existing verified credentials.
                                </li>
                                <li class="flex items-start gap-2 text-[9px] font-bold text-purple-700/80 uppercase">
                                    <span class="text-purple-400">•</span> All administrative operations are irreversibly logged.
                                </li>
                                <li class="flex items-start gap-2 text-[9px] font-bold text-purple-700/80 uppercase">
                                    <span class="text-purple-400">•</span> Access termination can only be executed by Root Admins.
                                </li>
                            </ul>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-4 border-t border-gray-50">
                            <a href="{{ route('admin.admins.index') }}" 
                               class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">
                                Abort Transaction
                            </a>
                            <button type="submit" 
                                    class="w-full sm:w-auto px-10 py-4 bg-gray-900 hover:bg-purple-600 text-white rounded-[1.5rem] font-black text-xs uppercase tracking-widest transition-all shadow-xl flex items-center justify-center gap-3">
                                <i class="fas fa-plus-circle"></i>
                                Provision Account
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Script for visual feedback on radio selection --}}
<script>
    document.querySelectorAll('input[name="admin_role"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Script logic preserved for behavior, though Peer-checked CSS handles most styling
            console.log("Role updated to: " + this.value);
        });
    });
</script>
@endsection