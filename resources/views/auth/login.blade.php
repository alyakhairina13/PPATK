<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SANTRIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex items-center justify-center p-[12px] md:p-[24px] antialiased">
    <main class="w-full max-w-[360px] glass-card rounded-xl p-[18px] md:p-[24px] flex flex-col gap-[18px] md:gap-[20px]">
        <!-- Header / Branding -->
        <header class="text-center flex flex-col gap-[6px]">
            <h1 class="text-[30px] leading-[1.08] tracking-tight font-bold text-on-surface" style="font-family: 'Inter', sans-serif;">SANTRIS</h1>
            <div class="mt-[6px]">
                <h2 class="text-[13px] leading-[1.25] tracking-tight font-semibold text-on-surface" style="font-family: 'Inter', sans-serif;">Welcome back</h2>
                <p class="text-[13px] leading-[1.4] tracking-tight text-secondary mt-[4px]" style="font-family: 'Inter', sans-serif;">Enter your credentials to continue.</p>
            </div>
        </header>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="p-3 bg-[#ffdad6] border border-error rounded-md">
                <div class="flex items-start">
                    <span class="material-symbols-outlined text-error mr-2 flex-shrink-0 mt-0.5" style="font-size: 20px;">error</span>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="text-xs text-error" style="font-family: 'Inter', sans-serif;">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-[14px] mt-[4px]">
            @csrf

            <!-- Username Field -->
            <div class="flex flex-col gap-[4px]">
                <label class="text-[12px] leading-[1.25] tracking-tight font-medium text-on-surface-variant ml-[4px]" for="username" style="font-family: 'Inter', sans-serif;">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="w-full bg-surface-pearl border border-hairline rounded-md px-[14px] py-[10px] text-[13px] leading-[1.35] tracking-tight text-on-surface focus:outline-none focus:ring-2 focus:ring-interactive-focus focus:border-transparent transition-shadow placeholder:text-text-muted @error('username') border-error @enderror"
                    style="font-family: 'Inter', sans-serif;"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    placeholder="Enter your username"
                >
            </div>

            <!-- Password Field -->
            <div class="flex flex-col gap-[4px]">
                <label class="text-[12px] leading-[1.25] tracking-tight font-medium text-on-surface-variant ml-[4px]" for="password" style="font-family: 'Inter', sans-serif;">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full bg-surface-pearl border border-hairline rounded-md px-[14px] py-[10px] text-[13px] leading-[1.35] tracking-tight text-on-surface focus:outline-none focus:ring-2 focus:ring-interactive-focus focus:border-transparent transition-shadow placeholder:text-text-muted @error('password') border-error @enderror"
                    style="font-family: 'Inter', sans-serif;"
                    required
                    placeholder="Enter your password"
                >
            </div>

            <!-- Options Row -->
            <div class="flex items-center justify-between px-[4px] mt-[2px]">
                <label class="flex items-center gap-[8px] cursor-pointer group">
                    <input
                        type="checkbox"
                        name="remember"
                        class="w-4 h-4 rounded border-hairline bg-surface-pearl text-primary-container focus:ring-interactive-focus transition-colors"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <span class="text-[12px] leading-[1.25] tracking-tight font-medium text-secondary group-hover:text-on-surface transition-colors" style="font-family: 'Inter', sans-serif;">Remember Me</span>
                </label>
                <a class="text-[12px] leading-[1.25] tracking-tight font-medium text-primary hover:text-interactive-focus transition-colors" href="#" style="font-family: 'Inter', sans-serif;">
                    Forgot Password?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-primary-container text-on-primary rounded-md py-[10px] text-[13px] leading-none tracking-tight font-medium active:scale-[0.98] transition-transform mt-[4px] flex items-center justify-center gap-[8px] shadow-sm hover:bg-interactive-focus" style="font-family: 'Inter', sans-serif;">
                Sign In
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_forward</span>
            </button>
        </form>

        <!-- Footer Note -->
        <footer class="text-center mt-[4px]">
            <p class="text-[11px] leading-none tracking-tight text-text-muted" style="font-family: 'Inter', sans-serif;">
                Securely managed by SANTRIS Legal Systems.
            </p>
        </footer>
    </main>
</body>
</html>
