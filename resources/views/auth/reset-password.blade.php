<x-guest-layout>
    <div class="fixed inset-0 flex flex-col md:flex-row bg-white overflow-hidden">
        
        <div class="relative w-full md:w-[60%] h-1/2 md:h-full overflow-hidden">
            <img src="{{ asset('images/LogIn.png') }}" class="absolute inset-0 w-full h-full object-cover" alt="City Arch">
        </div>

        <div class="relative w-full md:w-[40%] h-1/2 md:h-full flex flex-col items-center justify-center p-6 md:p-12 bg-cover bg-center" 
             style="background-image: url('{{ asset('images/PawsBackground.png') }}');"> 
            
            <div class="w-full max-w-sm">
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/LogoBlack.png') }}" class="h-100 w-100 drop-shadow-md object-contain" alt="City Seal">
                </div>

                <div class="bg-white rounded-[2rem] shadow-2xl p-8 border border-gray-100">
                    
                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Reset Password</h2>
                        <p class="text-sm text-gray-500 mt-1">Create your new password</p>
                    </div>

                    {{-- ERROR BLOCK --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                            <ul class="list-disc list-inside text-[15px] font-bold tracking-tight">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div>
                            <x-input-label for="email" value="Email Address" class="text-[15px] black-600 font-bold text-xs" />
                            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" 
                                class="block mt-1 w-full border-gray-300 rounded-xl py-2 focus:ring-red-500 focus:border-red-500 shadow-sm bg-gray-50" 
                                required readonly>
                        </div>

                        <div>
                            <x-input-label for="password" value="New Password" class="text-[15px] black-600 font-bold text-xs" />
                            <div class="relative mt-1">
                                <input id="password" type="password" name="password" 
                                    class="block w-full border-gray-300 rounded-xl py-2 pr-10 focus:ring-red-500 focus:border-red-500 shadow-sm" 
                                    required placeholder="Minimum 8 characters">
                                <button type="button" onclick="togglePassword('password', 'eye-icon-1')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-red-700">
                                    <svg id="eye-icon-1" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" value="Confirm Password" class="text-[15px] black-600 font-bold text-xs" />
                            <div class="relative mt-1">
                                <input id="password_confirmation" type="password" name="password_confirmation" 
                                    class="block w-full border-gray-300 rounded-xl py-2 pr-10 focus:ring-red-500 focus:border-red-500 shadow-sm" 
                                    required placeholder="Re-enter your password">
                                <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-2')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-red-700">
                                    <svg id="eye-icon-2" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-2">
                            <a href="{{ route('login') }}" class="text-[12px] font-black uppercase tracking-widest text-gray-900 hover:underline">
                                ← Cancel
                            </a>
                            <button type="submit" class="bg-red-800 hover:bg-red-900 text-white font-bold py-2 px-6 rounded-xl shadow-md transition-transform active:scale-95 uppercase text-xs tracking-widest">
                                Reset Password
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