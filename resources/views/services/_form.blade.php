@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Nome --}}
    <div>
        <label class="block text-sm font-medium mb-2">Nome</label>
        <input type="text"
               name="name"
               value="{{ old('name', $service->name ?? '') }}"
               class="w-full border rounded-lg p-3">
        @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Preço --}}
    <div>
        <label class="block text-sm font-medium mb-2">Preço</label>
        <input type="number"
               step="0.01"
               name="price"
               value="{{ old('price', $service->price ?? '') }}"
               class="w-full border rounded-lg p-3">
        @error('price') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Tipo --}}
    <div>
        <label class="block text-sm font-medium mb-2">Tipo</label>

        <select name="billing_type" class="w-full border rounded-lg p-3">
            <option value="monthly"
                {{ old('billing_type', $service->billing_type ?? '') == 'monthly' ? 'selected' : '' }}>
                Mensal
            </option>

            <option value="one_time"
                {{ old('billing_type', $service->billing_type ?? '') == 'one_time' ? 'selected' : '' }}>
                Único
            </option>
        </select>
    </div>

    {{-- Ativo --}}
    <div class="flex items-center mt-8">
        <input type="checkbox"
               name="is_active"
               value="1"
               {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }}
               class="w-4 h-4">
        <label class="ml-2 text-sm">Ativo</label>
    </div>

</div>

{{-- Descrição --}}
<div class="mt-6">
    <label class="block text-sm font-medium mb-2">Descrição</label>

    <textarea name="description"
              class="w-full border rounded-lg p-3"
              rows="4">{{ old('description', $service->description ?? '') }}</textarea>
</div>

{{-- Botões --}}
<div class="mt-6 flex gap-4">
    <button class="px-6 py-3 bg-school-primary text-white rounded-lg">
        Guardar
    </button>

    <a href="{{ route('services.index') }}"
       class="px-6 py-3 border rounded-lg">
        Cancelar
    </a>
</div>