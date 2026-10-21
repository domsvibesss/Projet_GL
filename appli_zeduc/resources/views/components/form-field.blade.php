{{-- Un champ de formulaire : étiquette + case + message d'erreur --}}
@props(['label', 'name', 'type' => 'text', 'value' => '', 'readonly' => false])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm">{{ $label }}</label>

    <input id="{{ $name }}"
           name="{{ $name }}"
           type="{{ $type }}"
           value="{{ old($name, $value) }}"
           @readonly($readonly)
           {{ $attributes->merge(['class' => 'w-full rounded-xl border border-primary px-4 py-3 text-black ' . ($readonly ? 'bg-primary/20' : 'bg-white')]) }}>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>