@props(['title' => 'ダッシュボード'])
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title }} - {{ config('app.name','App') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>
<body class="bg-gray-50">
  <header class="bg-white border-b">
    <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="font-semibold">{{ config('app.name', 'App') }}</div>
      <nav class="text-sm">
        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
      </nav>
    </div>
  </header>
  <main class="max-w-5xl mx-auto px-6 py-6">
    {{ $slot }}
  </main>
  @livewireScripts
</body>
</html>
