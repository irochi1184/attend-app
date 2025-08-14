<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- ヘッダー -->
            <header class="bg-gray-800 text-white">
            <div class="container mx-auto flex items-center justify-between p-4">
                <!-- 左側ナビ -->
                <nav class="flex items-center space-x-6">
                    <a href="{{ route('attendance.register') }}" class="hover:underline">出席登録</a>
                    <a href="{{ route('attendance.list') }}" class="hover:underline">出席一覧</a>
                </nav>

                <!-- 右側ユーザーメニュー -->
                <div class="flex items-center space-x-4">
                <a href="{{ route('mypage') }}" class="hover:underline">マイページ</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hover:underline">ログアウト</button>
                </form>
                </div>
            </div>
            </header>

            <main class="p-6">
            {{ $slot }}
            </main>

        </div>
    </body>
</html>
