<x-guest-layout>
    <div class="fixed inset-0 flex flex-col md:flex-row bg-white overflow-auto">
        
        {{-- Left Side: Image (Matching Login Structure) --}}
        <div class="w-auto h-full hidden md:block">
            <img src="{{ asset('images/LogIn.png') }}" 
                class="w-auto h-full block object-top" 
                alt="City Arch">
        </div>

        {{-- Right Side: Form (Matching Login Background & Padding) --}}
        <div class="relative flex-1 h-full flex flex-col items-center justify-center p-6 md:p-12 bg-cover bg-center shrink-0" 
            style="background-image: url('{{ asset('images/PawsBackground.png') }}');">
            
            <div class="w-full max-w-sm">
                {{-- Logo Section --}}
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/Logo.png') }}" class="h-100 w-100 drop-shadow-md object-contain" alt="City Seal">
                </div>

                {{-- Forgot Password Card --}}
                <div class="bg-white rounded-[2rem] shadow-2xl p-8 border border-gray-100">
                    
                    <div class="text-center mb-6">
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-tighter leading-none">Forgot Password</h2>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Enter your email to reset</p>
                    </div>

                    {{-- Success & Reset Link Blocks --}}
                    @if (session('status'))
                        <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                            <p class="text-[13px] font-bold">{{ session('status') }}</p>
                        </div>
                    @endif

                    @if (session('reset_link'))
                        <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded-lg shadow-sm">
                            <p class="text-[11px] font-black uppercase tracking-widest mb-1">🔗 Dev Link:</p>
                            <a href="{{ session('reset_link') }}" class="text-[11px] text-blue-600 break-all hover:underline font-bold">
                                {{ session('reset_link') }}
                            </a>
                        </div>
                    @endif

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

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <div>
                            <x-input-label for="email" value="Email Address" class="text-[15px] text-gray-900 font-bold text-xs" />
                            <input id="email" type="email" name="email" value="{{ old('email') }}" 
                                class="block mt-1 w-full border-gray-300 rounded-xl py-2 focus:ring-red-500 focus:border-red-500 shadow-sm text-sm" 
                                required autofocus placeholder="name@example.com">
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-2">
                            <a href="{{ route('login') }}" class="text-[11px] font-black uppercase tracking-widest text-gray-900 hover:text-red-700 transition-colors">
                                ← Back to Login
                            </a>
                            <button type="submit" class="bg-red-800 hover:bg-red-900 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-transform active:scale-95 uppercase text-[10px] tracking-widest">
                                Send Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>