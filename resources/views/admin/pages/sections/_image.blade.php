@props(['name', 'label', 'current' => null])
@php($url = $current ? \Illuminate\Support\Facades\Storage::disk('public')->url($current) : null)
<div x-data="{ removed: false }">
    <label class="field-label">{{ $label }}</label>
    @if ($url)
        <div class="mb-2 flex items-center gap-3" x-show="!removed">
            <img src="{{ $url }}" class="h-16 w-16 rounded-xl object-cover" alt="">
            <label class="flex items-center gap-2 text-xs text-rose-600">
                <input type="checkbox" name="remove_{{ $name }}" value="1" x-model="removed" class="rounded border-slate-300 text-rose-600">
                إزالة
            </label>
        </div>
    @endif
    <input name="{{ $name }}" type="file" accept="image/png,image/jpeg,image/webp" class="field-input">
    @error($name) <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
