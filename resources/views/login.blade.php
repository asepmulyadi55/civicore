<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>CiviCore | Admin Login</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Manrope"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        body {
            font-family: 'Manrope', sans-serif;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark font-display min-h-screen flex flex-col transition-colors duration-200">
    <main class="flex-grow flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center p-3 bg-primary/10 rounded-xl mb-4">
                    <span class="material-icons text-primary text-4xl">apartment</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">CiviCore</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Internal Admin Dashboard</p>
            </div>
            <div
                class="bg-white dark:bg-slate-900/50 dark:border dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-xl overflow-hidden">
                <div class="p-8">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Sign In</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Access the community management
                            system</p>
                    </div>

                    @if (session('success'))
                        <div
                            class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900/30 rounded-lg">
                            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/30 rounded-lg">
                            <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form action="/login" class="space-y-5" method="POST" novalidate>
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5"
                                for="username">
                                Username or Email
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-slate-400 text-sm">alternate_email</span>
                                </span>
                                <input
                                    class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none @error('username') border-red-500 dark:border-red-500 @enderror"
                                    id="username" name="username" placeholder="admin@civicore.com" type="text"
                                    value="{{ old('username') }}" />
                            </div>
                            @error('username')
                                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    for="password">
                                    Password
                                </label>
                                <a class="text-xs font-bold text-primary hover:text-primary/80 transition-colors"
                                    href="#">
                                    Forgot Password?
                                </a>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-slate-400 text-sm">lock_outline</span>
                                </span>
                                <input
                                    class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 outline-none @error('password') border-red-500 dark:border-red-500 @enderror"
                                    id="password" name="password" placeholder="••••••••" type="password" />
                                <button
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    type="button" onclick="togglePassword()">
                                    <span class="material-icons text-sm" id="toggleIcon">visibility</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center">
                            <input
                                class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary dark:bg-slate-800 dark:border-slate-700"
                                id="remember" name="remember" type="checkbox" />
                            <label class="ml-2 text-sm text-slate-600 dark:text-slate-400" for="remember">Remember this
                                device</label>
                        </div>
                        <button
                            class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-primary/20 transition-all duration-200 flex items-center justify-center space-x-2"
                            type="submit">
                            <span>Login to Dashboard</span>
                        </button>
                    </form>
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-white dark:bg-slate-900 px-4 text-slate-500 font-semibold tracking-wider">Or
                                continue with</span>
                        </div>
                    </div>
                    <button
                        class="w-full flex items-center justify-center space-x-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 py-3 px-4 rounded-lg transition-all duration-200 group"
                        type="button">
                        <img alt="Google" class="w-5 h-5"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuB9G5NNslA1PH78AwWG_MxCoc1WVSST8Bais319cLNapq9nkxCbWmgQ4-6QCWbj--gmK2tUjTWbFf5CF6A7QbP7o9ZbmMaKUvCH2dZM7vNSlkCa7e8btXobYpGXIANxR6DWgIDjteV-4xAh31PWNtW3ysVi4HwlYkVewEsEvrofiomAT6BB0lErMx1Cd2xJpnDKuSbm3xKskHPpRzbh8tgG__hNfFKTfiBH-I39QaBGG4_pdm4UlsVmuCpMGSH2X51hQqUziWw_DgM" />
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Sign in with Google</span>
                    </button>
                </div>
                <div
                    class="p-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 text-center">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Don't have an account?
                        <a class="text-primary font-bold hover:underline" href="{{ url('/register') }}">Register a new
                            account</a>
                    </p>
                </div>
            </div>
            <div
                class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest font-medium">
                <p>© 2024 CiviCore Management System • v2.4.0</p>
                <div class="mt-2 space-x-4">
                    <a class="hover:text-primary transition-colors" href="#">Security</a>
                    <a class="hover:text-primary transition-colors" href="#">Privacy</a>
                    <a class="hover:text-primary transition-colors" href="#">Support</a>
                </div>
            </div>
        </div>
    </main>
    <div class="fixed bottom-6 right-6">
        <button
            class="p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-primary transition-all duration-200"
            onclick="document.documentElement.classList.toggle('dark')">
            <span class="material-icons">dark_mode</span>
        </button>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.textContent = 'visibility_off';
            } else {
                passwordField.type = 'password';
                toggleIcon.textContent = 'visibility';
            }
        }
    </script>
</body>

</html>