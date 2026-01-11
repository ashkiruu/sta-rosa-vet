<x-guest-layout>
    <h2 class="text-2xl font-bold mb-4">Step 3: Account Setup</h2>
    @if(session('ocr_status'))
        <div class="mb-6 p-4 rounded-lg border {{ session('ocr_status') == 'Verified' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-yellow-50 border-yellow-200 text-yellow-800' }}">
            <div class="flex items-center">
                <span class="text-xl mr-2">{{ session('ocr_status') == 'Verified' ? '✅' : '⏳' }}</span>
                <div>
                    <p class="font-bold">OCR Status: {{ session('ocr_status') }}</p>
                    <p class="text-sm">{{ session('ocr_message') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.step3.post') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Password Confirmation -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required />
        </div>

        <div class="mt-6">
            <x-primary-button>{{ __('Complete Registration') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
