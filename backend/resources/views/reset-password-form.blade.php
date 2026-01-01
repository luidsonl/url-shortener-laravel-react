
<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="bg-white rounded-lg shadow-md w-full max-w-md p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">Reset Password</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" value="{{ $email }}" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">New Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition">
                Reset Password
            </button>
        </form>
    </div>
</div>