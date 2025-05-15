<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [data-bs-theme="dark"] body {
            background-color: #121212;
            color: #ffffff;
        }
        [data-bs-theme="dark"] .card {
            background-color: #1e1e1e;
        }
        [data-bs-theme="dark"] .modal-content {
            background-color: #1e1e1e;
            color: #ffffff;
        }
        .form-select, .form-control {
            height: 38px;
        }

        .btn-filter {
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <title>TodoEz</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Atau jika ingin menggunakan Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body>
    <div id="app">
        @include('partials.navbar')

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Tambahkan ini -->
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js">
        document.addEventListener('livewire:load', function() {
            // Re-init Livewire ketika modal dibuka
            $('.modal').on('shown.bs.modal', function() {
                Livewire.rescan();
            });
        });
        document.addEventListener('livewire:init', function() {
            // Inisialisasi ulang Livewire saat modal dibuka
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    Livewire.rescan();
                });
            });
        });
        document.addEventListener('livewire:load', function() {
            new Sortable(document.querySelector('tbody'), {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function() {
                    // Implementasi update urutan bisa ditambahkan
                    console.log('Item di-reorder');
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;

        // Cek localStorage
        const theme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', theme);
        toggle.checked = theme === 'dark';

        // Saat toggle diubah
        toggle.addEventListener('change', function () {
            const newTheme = this.checked ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    });
    </script>
</body>

</html>
