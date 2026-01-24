<x-guest-layout>
    <div class="fixed inset-0 flex flex-col md:flex-row bg-white overflow-auto">
        
        {{-- Left Side: Image (Consistent Scaling) --}}
        <div class="w-auto h-full hidden md:block">
            <img src="{{ asset('images/LogIn.png') }}" 
                class="w-auto h-full block object-top" 
                alt="City Arch">
        </div>

        {{-- Right Side: Form Container --}}
        <div class="relative flex-1 h-full flex flex-col items-center justify-center p-6 md:p-12 bg-cover bg-center shrink-0" 
            style="background-image: url('{{ asset('images/PawsBackground.png') }}');">
            
            <div class="w-full max-w-sm">
                {{-- Logo Section --}}
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/Logo.png') }}" class="h-100 w-100 drop-shadow-md object-contain" alt="City Seal">
                </div>

                {{-- Reset Card --}}
                <div class="bg-white rounded-[2rem] shadow-2xl p-8 border border-gray-100">
                    
                    <div class="text-center mb-6">
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-tighter leading-none">Reset Password</h2>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Create your new security key</p>
                    </div>

                    {{-- Error Block --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                            <ul class="list-disc list-inside text-[13px] font-bold tracking-tight">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Email (Read Only) --}}
                        <div>
                            <x-input-label for="email" value="Email Account" class="text-gray-900 font-bold text-xs" />
                            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" 
                                class="block mt-1 w-full border-gray-200 rounded-xl py-2 px-4 bg-gray-50 text-gray-500 text-sm font-semibold outline-none cursor-not-allowed" 
                                required readonly>
                        </div>

                        {{-- New Password --}}
                        <div>
                            <x-input-label for="password" value="New Password" class="text-gray-900 font-bold text-xs" />
                            <div class="relative mt-1">
                                <input id="password" type="password" name="password" 
                                    class="block w-full border-gray-300 rounded-xl py-2 px-4 focus:ring-red-500 focus:border-red-500 shadow-sm text-sm" 
                                    required placeholder="Min. 8 characters">
                                <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-red-700">
                                    <svg id="eye-icon-1" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <x-input-label for="password_confirmation" value="Confirm New Password" class="text-gray-900 font-bold text-xs" />
                            <div class="relative mt-1">
                                <input id="password_confirmation" type="password" name="password_confirmation" 
                                    class="block w-full border-gray-300 rounded-xl py-2 px-4 focus:ring-red-500 focus:border-red-500 shadow-sm text-sm" 
                                    required placeholder="Repeat password">
                                <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-red-700">
                                    <svg id="eye-icon-2" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-4">
                            <a href="{{ route('login') }}" class="text-[11px] font-black uppercase tracking-widest text-gray-900 hover:text-red-700 transition-colors">
                                ← Cancel
                            </a>
                            <button type="submit" class="bg-red-800 hover:bg-red-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-transform active:scale-95 uppercase text-[10px] tracking-widest">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</x-guest-layout>