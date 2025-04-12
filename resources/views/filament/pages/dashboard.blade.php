<x-filament::widget>
    <style>
        @media (min-width: 1024px) {
            p.text-below {
                margin-left: 10rem !important;
                margin-right: 10rem !important;
            }
        }
        .margin-bottom-content {
            margin-bottom: 180px !important;
        }
    </style>
    <div class="flex flex-col items-center h-screen margin-bottom-content">
        <img src="{{ asset('images/perpustakaan.jpg') }}" alt="Jumbotron" class="w-full h-64 object-cover mb-4">
        
        <p class="text-xl font-bold text-gray-800 text-center text-below" style="margin-bottom: 50px;">
            Perpustakaan SDN Bojongloa 1 Merupakan Perpustakaan yang berperan aktif dalam mewujudkan visi dan misi perpustakaan yakni menumbuhkan minat baca siswa serta mengoptimalkan pembelajaran dengan metode kurikulum sekolah
        </p>
        
        <div class="flex justify-center" style="gap: 96px;">
            @foreach (['instagram', 'tiktok', 'facebook'] as $icon)
                <div class="flex items-center justify-center" style="width: 48px; height: 48px;">
                    <a href="https://{{ $icon }}.com" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset("images/{$icon}.svg") }}" alt="{{ ucfirst($icon) }}" class="w-full h-full object-contain" />
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-filament::widget>
