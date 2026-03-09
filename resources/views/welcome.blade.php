<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => config('app.name')])
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 text-[#1b1b18] border border-transparent hover:border-[#19140035] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col lg:max-w-4xl lg:items-center lg:justify-center">
                <div class="text-center p-10 lg:p-20 bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] rounded-lg w-full">
                    <h1 class="mb-4 text-6xl font-bold text-[#1b1b18] leading-none">{{ config('app.name') }}</h1>
                    <p class="mb-8 text-2xl text-[#525252] leading-8">
                        Track and manage your home assets, schedule maintenance tasks,<br>
                        and stay on top of repairs &mdash; all in one place.
                    </p>
                    <div class="flex gap-4 justify-center">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-block px-8 py-4 bg-[#171717] text-white text-lg font-semibold rounded-lg shadow-lg hover:bg-black leading-7">
                                Go to Dashboard
                            </a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-[#171717] text-white text-lg font-semibold rounded-lg shadow-lg hover:bg-black leading-7">
                                    Register
                                </a>
                            @endif
                            <a href="{{ route('login') }}" class="inline-block px-8 py-4 bg-white text-[#171717] text-lg font-semibold rounded-lg border-2 border-[#e5e5e5] hover:border-[#d4d4d4] leading-7">
                                Sign In
                            </a>
                        @endauth
                    </div>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
