@props(['items' => [], 'max' => 8, 'label' => 'العناصر'])
<div x-data="{
        items: {{ Illuminate\Support\Js::from(!empty($items) ? array_values($items) : [['icon'=>'','title'=>'','description'=>'']]) }},
        max: {{ $max }},
        add() { if (this.items.length < this.max) this.items.push({icon:'',title:'',description:''}) },
        remove(i) { this.items.splice(i, 1) },
    }">
    <div class="mb-2 flex items-center justify-between">
        <label class="field-label !mb-0">{{ $label }}</label>
        <button type="button" @click="add()" x-show="items.length < max"
                class="rounded-lg bg-brand-50 px-2 py-1 text-xs font-bold text-brand-600">＋ إضافة</button>
    </div>
    <div class="space-y-2">
        <template x-for="(item, i) in items" :key="i">
            <div class="rounded-xl bg-slate-50 p-3">
                <div class="flex items-center gap-2">
                    <input type="text" :name="`items[${i}][icon]`" x-model="item.icon" maxlength="4"
                           placeholder="😀" class="field-input !w-16 text-center">
                    <input type="text" :name="`items[${i}][title]`" x-model="item.title"
                           placeholder="العنوان" class="field-input flex-1">
                    <button type="button" @click="remove(i)" class="grid h-9 w-9 flex-none place-items-center rounded-xl bg-rose-50 text-rose-500">✕</button>
                </div>
                <input type="text" :name="`items[${i}][description]`" x-model="item.description"
                       placeholder="وصف مختصر (اختياري)" class="field-input mt-2">
            </div>
        </template>
    </div>
</div>
