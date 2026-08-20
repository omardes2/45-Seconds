<div>
    <label class="field-label">السؤال</label>
    <input name="question" value="{{ old('question', $faq->question ?? '') }}" class="field-input" required>
    @error('question') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
</div>
<div>
    <label class="field-label">الإجابة</label>
    <textarea name="answer" rows="3" class="field-input" required>{{ old('answer', $faq->answer ?? '') }}</textarea>
    @error('answer') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
</div>
<label class="flex items-center gap-2 text-sm text-slate-600">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true)) class="rounded border-slate-300 text-brand-600">
    ظاهر للزوار
</label>
