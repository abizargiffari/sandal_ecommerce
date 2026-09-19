@php
    $category = $category ?? null;
@endphp

<div class="mb-4">
    <label for="name" class="block text-sm font-medium mb-1">Nama Kategori</label>
    <input type="text" name="name" id="name" required
        value="{{ old('name', $category->name ?? '') }}"
        class="w-full border rounded px-3 py-2 text-sm">
</div>

<div class="mb-4">
    <label for="parent_id" class="block text-sm font-medium mb-1">Kategori Induk (opsional)</label>
    <select name="parent_id" id="parent_id" class="w-full border rounded px-3 py-2 text-sm">
        <option value="">- Tidak ada (kategori utama) -</option>
        @foreach ($parentCategories as $parent)
            <option value="{{ $parent->id }}"
                @selected(old('parent_id', $category->parent_id ?? '') == $parent->id)>
                {{ $parent->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label for="description" class="block text-sm font-medium mb-1">Deskripsi (opsional)</label>
    <textarea name="description" id="description" rows="3"
            class="w-full border rounded px-3 py-2 text-sm">{{ old('description', $category->description ?? '') }}</textarea>
</div>

<div class="mb-4">
    <label for="image" class="block text-sm font-medium mb-1">Gambar Kategori</label>
    @if (isset($category) && $category->image)
        <img src="{{ Storage::url($category->image) }}" class="w-16 h-16 object-cover rounded mb-2">
    @endif
    <input type="file" name="image" id="image" accept="image/*" class="w-full text-sm">
    <p class="text-xs text-gray-400 mt-1">Format JPG/PNG, maksimal 2MB.</p>
</div>

<div class="mb-4 flex items-center">
    <input type="checkbox" name="is_active" id="is_active" class="mr-2"
        @checked(old('is_active', $category->is_active ?? true))>
    <label for="is_active" class="text-sm">Aktifkan kategori ini</label>
</div>
