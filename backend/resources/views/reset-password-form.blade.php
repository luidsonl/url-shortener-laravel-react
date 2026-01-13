@extends('layouts.app')

@section('content')
<div class="relative min-h-screen flex flex-col justify-center overflow-hidden py-12 bg-white">
    <div class="absolute inset-0 bg-[url(https://play.tailwindcss.com/img/grid.svg)] bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>

    <div class="relative px-6 pt-10 pb-8 bg-white shadow-2xl ring-1 ring-gray-900/5 sm:max-w-md sm:mx-auto sm:rounded-3xl sm:px-10 border border-slate-100/50 backdrop-blur-md bg-white/90">
        <div class="max-w-md mx-auto">
            <div class="flex flex-col items-center mb-10">
                <div class="h-12 w-12 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-200 mb-4 transform -rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Security Check</h1>
                <p class="text-slate-500 mt-2 text-center text-sm">Please set a strong password to protect your account.</p>
            </div>

            @if ($errors->any())
                <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm" role="alert">
                    <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-bold text-sm">Correct requested items:</span>
                    </div>
                    <ul class="text-xs space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="space-y-1">
                    <label for="email_display" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Registered Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email_display" type="email" value="{{ $email }}" disabled 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm cursor-not-allowed transition-all duration-200 shadow-inner">
                    </div>
                </div>

                <div class="space-y-1 text-slate-900">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-500">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                       </div>
                        <input id="password" type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all duration-200 text-sm placeholder-slate-300 shadow-sm outline-hidden">
                    </div>
                </div>

                <div class="space-y-1 text-slate-900">
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Confirm Your Identity</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all duration-200 text-sm placeholder-slate-300 shadow-sm outline-hidden">
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-bold py-4 rounded-xl transition-all duration-200 shadow-lg shadow-brand-500/20 flex items-center justify-center space-x-2">
                    <span>Finalize Secure Update</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection