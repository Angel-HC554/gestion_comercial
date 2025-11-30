{{-- app/views/components/flux-button.blade.php --}}
@php
    // 1. Valores por defecto
    $type = $type ?? 'button';
    $variant = $variant ?? 'primary'; // primary, outline, ghost, danger
    $size = $size ?? 'base'; // base, sm, xs
    $icon = $icon ?? null; // true si solo es un icono cuadrado
    
    // 2. Lógica de Tamaños (Réplica exacta de Flux)
    $sizeClasses = match ($size) {
        'base' => 'h-10 text-sm rounded-lg ' . ($icon ? 'w-10 px-0' : 'px-4 py-2'),
        'sm'   => 'h-8 text-sm rounded-md ' . ($icon ? 'w-8 px-0' : 'px-3'),
        'xs'   => 'h-6 text-xs rounded-md ' . ($icon ? 'w-6 px-0' : 'px-2'),
        default => 'h-10 text-sm rounded-lg px-4'
    };

    // 3. Lógica de Variantes (Los colores y sombras mágicas)
    // Aquí he fijado el color "Emerald" como tu color de acento principal
    $variantClasses = match ($variant) {
        'primary' => '
            bg-emerald-600 
            hover:bg-emerald-500 
            text-white 
            border border-black/10 
            shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.2)]
        ',
        'outline' => '
            bg-white 
            hover:bg-zinc-50 
            text-zinc-800 
            border border-zinc-200 border-b-zinc-300/80 
            shadow-sm
        ',
        'ghost' => '
            bg-transparent 
            hover:bg-zinc-800/5 
            text-zinc-800 
            border-transparent
            shadow-none
        ',
        'danger' => '
            bg-red-500 
            hover:bg-red-600 
            text-white 
            border border-black/10 
            shadow-[inset_0px_1px_0px_0px_rgba(255,255,255,0.15)]
        ',
        default => ''
    };
@endphp

<button type="{{ $type }}"
    class="
        {{-- Clases Base --}}
        relative inline-flex items-center justify-center gap-2 whitespace-nowrap font-medium
        transition-all duration-150
        focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-600
        disabled:opacity-50 disabled:pointer-events-none disabled:cursor-not-allowed
        
        {{-- Inyección de estilos calculados --}}
        {{ $sizeClasses }}
        {{ $variantClasses }}
        
        {{-- Clases extra pasadas desde la vista --}}
        {{ $class ?? '' }}
    ">
    {{ $slot }}
</button>