<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Estudantes
                </h1>
                <p class="text-gray-600">
                    Gestão de estudantes do sistema
                </p>
            </div>
           <a href="{{ route('students.create') }}" class="bg-school-primary text-white px-4 py-2 rounded-lg hover:bg-school-dark flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Novo Estudante</span>
                </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                        Código
                    </th>

                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                        Estudante
                    </th>

                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                        Turma
                    </th>

                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                        Encarregado
                    </th>

                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                        Serviços
                    </th>

                    <th class="text-right px-6 py-4 text-sm font-semibold text-gray-600">
                        Ações
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- Código --}}
                        <td class="px-6 py-5">
                            <div class="font-medium text-gray-900">
                                {{ $student->student_code }}
                            </div>
                        </td>

                        {{-- Estudante --}}
                        <td class="px-6 py-5">
                            <div class="font-semibold text-gray-900">
                                {{ $student->name }}
                            </div>

                            <div class="text-sm text-gray-500">
                                {{ $student->email }}
                            </div>
                        </td>

                        {{-- Turma --}}
                        <td class="px-6 py-5 text-gray-700">
                            {{ $student->class }}
                        </td>
                        {{-- Encarregado --}}
                        <td class="px-6 py-5">
                            <div class="font-medium text-gray-800">
                                {{ $student->guardian->name ?? '---' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $student->guardian->email ?? '' }}
                            </div>
                        </td>

                        {{-- Serviços --}}
                        <td class="px-6 py-5">
                            <div class="flex flex-wrap gap-2">
                                @forelse($student->services->take(2) as $service)
                                    <span class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full">
                                        {{ $service->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 text-sm">
                                        Nenhum
                                    </span>
                                @endforelse
                                @if($student->services->count() > 2)
                                    <span class="bg-gray-100 text-gray-600 text-xs px-3 py-1 rounded-full">
                                        +{{ $student->services->count() - 2 }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Ações --}}
                        <td class="px-6 py-5">
                            <div class="flex justify-end items-center gap-4">
                                <a href="{{ route('students.show', $student) }}"
                                   class="text-school-primary hover:underline text-sm font-medium">
                                    Ver
                                </a>

                                <a href="{{ route('students.edit', $student) }}"
                                   class="text-blue-600 hover:underline text-sm font-medium">
                                    Editar
                                </a>

                                <form action="{{ route('students.destroy', $student) }}"
                                      method="POST"
                                      onsubmit="return confirm('Deseja remover este estudante?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:underline text-sm font-medium">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="px-6 py-10 text-center text-gray-500">
                            Nenhum estudante encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Paginação --}}
    <div class="mt-6">
        {{ $students->links() }}
    </div>
</x-app-layout>