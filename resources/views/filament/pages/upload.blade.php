<x-filament::page>
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach (App\Filament\Pages\Report\Data::getSubMenuItems() as $item)
            @php
                $currentRoute = static::getRouteName();
                $isActive = $currentRoute === $item['route'];
            @endphp
    
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    'bg-primary-600 text-white shadow' => $isActive,
                    'text-gray-600 hover:bg-gray-100' => ! $isActive,
                ])
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    {{ $this->table }}
</x-filament::page>
