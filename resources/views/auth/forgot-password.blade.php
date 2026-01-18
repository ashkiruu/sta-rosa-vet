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
                        <h2 class="text-xl font-bold text-gray-800">Forgot Password</h2>
                        <p class="text-sm text-gray-500 mt-1">Enter your email to reset your password</p>
                    </div>

                    {{-- SUCCESS MESSAGE --}}
                    @if (session('status'))
                        <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                            <p class="text-[14px] font-semibold">{{ session('status') }}</p>
                        </div>
                    @endif

                    {{-- RESET LINK DISPLAY (Local Development Only) --}}
                    @if (session('reset_link'))
                        <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded-lg shadow-sm">
                            <p class="text-[13px] font-bold mb-2">🔗 Reset Link (Local Dev):</p>
                            <div class="bg-white p-2 rounded border border-blue-200 break-all">
                                <a href="{{ session('reset_link') }}" class="text-[12px] text-blue-600 hover:underline">
                                    {{ session('reset_link') }}
                                </a>
                            </div>
                            <p class="text-[11px] mt-2 text-blue-500">Click the link above or copy it to your browser.</p>
                        </div>
                    @endif

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

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <div>
                            <x-input-label for="email" value="Email Address" class="text-[15px] black-600 font-bold text-xs" />
                            <input id="email" type="email" name="email" value="{{ old('email') }}" 
                                class="block mt-1 w-full border-gray-300 rounded-xl py-2 focus:ring-red-500 focus:border-red-500 shadow-sm" 
                                required autofocus placeholder="Enter your registered email">
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-2">
                            <a href="{{ route('login') }}" class="text-[12px] font-black uppercase tracking-widest text-gray-900 hover:underline">
                                ← Back to Login
                            </a>
                            <button type="submit" class="bg-red-800 hover:bg-red-900 text-white font-bold py-2 px-6 rounded-xl shadow-md transition-transform active:scale-95 uppercase text-xs tracking-widest">
                                Send Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>