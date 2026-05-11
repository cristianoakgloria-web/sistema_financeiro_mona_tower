<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestão de Serviços</h1>
                <p class="text-gray-600">Gerir serviços adicionais dos estudantes</p>
            </div>
            <a href="{{ route('services.create') }}" class="bg-school-primary text-white px-4 py-2 rounded-lg hover:bg-school-dark transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Novo Serviço</span>
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left p-4">Serviço</th>
                        <th class="text-left p-4">Tipo</th>
                        <th class="text-left p-4">Preço</th>
                        <th class="text-left p-4">Estado</th>
                        <th class="text-right p-4">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($services as $service)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $service->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $service->description }}
                                </div>
                            </td>

                            <td class="p-4">
                                {{ $service->billing_type == 'monthly' ? 'Mensal' : 'Único' }}
                            </td>

                            <td class="p-4">
                                {{ number_format($service->price, 2, ',', '.') }} Kz
                            </td>

                            <td class="p-4">
                                @if($service->is_active)
                                    <span class="text-green-700 bg-green-100 px-3 py-1 rounded-full text-xs">
                                        Ativo
                                    </span>
                                @else
                                    <span class="text-red-700 bg-red-100 px-3 py-1 rounded-full text-xs">
                                        Inativo
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-right space-x-3">
                                <a href="{{ route('services.edit', $service) }}"
                                class="text-blue-600 hover:underline">
                                    Editar
                                </a>

                                <form action="{{ route('services.toggle-status', $service) }}"
                                    method="POST"
                                    class="inline">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="text-yellow-600 hover:underline">
                                        {{ $service->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>

                                <form action="{{ route('services.destroy', $service) }}"
                                    method="POST"
                                    class="inline"
                                    onsubmit="return confirm('Eliminar este serviço?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="text-red-600 hover:underline">
                                        Apagar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">
                                Nenhum serviço encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-6">
                {{ $services->links() }}
            </div>
        </div>
    </div>
</x-app-layout>