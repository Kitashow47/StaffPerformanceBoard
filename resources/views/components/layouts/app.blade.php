{{-- resources/views/components/layouts/app.blade.php --}}
@props(['title' => ''])

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ? $title.' | ' : '' }}Staff Performance</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
  <div class="min-h-screen">
    {{-- 任意のヘッダー領域（必要なら使う） --}}
    @isset($header)
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4">
          {{ $header }}
        </div>
      </header>
    @endisset

    <main class="py-6">
      {{ $slot }}
    </main>
  </div>

  @livewireScripts
</body>
</html>
