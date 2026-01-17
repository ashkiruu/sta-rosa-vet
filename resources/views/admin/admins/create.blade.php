@extends('layouts.admin')
@section('page_title', 'Create Admin Account')
@section('content')
<div class="mb-6">
    <a href="{{ route('admin.admins.index') }}" class="text-purple-600 hover:text-purple-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Admin List
    </a>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800">Add New Admin Account</h2>
            <p class="text-gray-500 mt-1">Grant administrative privileges to a verified user</p>
        </div>

        @if($eligibleUsers->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 fa-2x mb-2"></i>
                <p class="text-yellow-800 font-medium">No eligible users found</p>
                <p class="text-yellow-600 text-sm mt-1">All verified users are already admins, or there are no verified users in the system.</p>
            </div>
        @else
            <form action="{{ route('admin.admins.store') }}" method="POST">
                @csrf
                
                {{-- User Selection --}}
                <div class="mb-6">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select User <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" id="user_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Choose a verified user --</option>
                        @foreach($eligibleUsers as $user)
                            <option value="{{ $user->User_ID }}" {{ old('user_id') == $user->User_ID ? 'selected' : '' }}>
                                {{ $user->First_Name }} {{ $user->Last_Name }} ({{ $user->Email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i> Only verified users who are not already admins are shown.
                    </p>
                </div>

                {{-- Role Selection --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Admin Role <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('admin_role', 'staff') === 'staff' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}">
                            <input type="radio" name="admin_role" value="staff" {{ old('admin_role', 'staff') === 'staff' ? 'checked' : '' }} class="mt-1 mr-3 text-purple-600 focus:ring-purple-500">
                            <div>
                                <p class="font-medium text-gray-800">
                                    <i class="fas fa-user text-green-500 mr-2"></i> Staff
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Can verify users, manage appointments, create certificates, and generate reports. All actions are logged.
                                </p>
                            </div>
                        </label>
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('admin_role') === 'admin' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}">
                            <input type="radio" name="admin_role" value="admin" {{ old('admin_role') === 'admin' ? 'checked' : '' }} class="mt-1 mr-3 text-purple-600 focus:ring-purple-500">
                            <div>
                                <p class="font-medium text-gray-800">
                                    <i class="fas fa-user-tie text-blue-500 mr-2"></i> Administrator
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Same permissions as Staff. Can be expanded for additional responsibilities in the future.
                                </p>
                            </div>
                        </label>
                    </div>
                    @error('admin_role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Info Notice --}}
                <div class="mb-6 bg-gray-50 border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-shield-alt text-purple-500 mr-1"></i> Important Notes
                    </h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• The selected user will gain immediate access to the admin panel</li>
                        <li>• All actions performed by this admin will be logged in the system</li>
                        <li>• Only you (Super Admin) can remove their admin privileges</li>
                        <li>• The user will use their existing login credentials</li>
                    </ul>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.admins.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-user-plus mr-2"></i> Create Admin Account
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
    // Highlight selected role
    document.querySelectorAll('input[name="admin_role"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('input[name="admin_role"]').forEach(r => {
                r.closest('label').classList.remove('border-purple-500', 'bg-purple-50');
                r.closest('label').classList.add('border-gray-200');
            });
            if (this.checked) {
                this.closest('label').classList.remove('border-gray-200');
                this.closest('label').classList.add('border-purple-500', 'bg-purple-50');
            }
        });
    });
</script>
@endsection