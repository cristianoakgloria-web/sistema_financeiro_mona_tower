<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Fatura #{{ $invoice->invoice_number }}
                </h1>

                <p class="text-gray-600">
                    Detalhes completos da fatura
                </p>
            </div>

            <div class="flex space-x-2">

                {{-- Editar --}}
                <a href="{{ route('invoices.edit', $invoice) }}"
                   class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition flex items-center space-x-2">

                    <span>Editar</span>
                </a>

                {{-- Pagamento --}}
                @if($invoice->balance > 0)

                    <a href="{{ route('invoices.payments.create', $invoice) }}"
                       class="bg-school-primary text-white px-4 py-2 rounded-lg hover:bg-school-dark transition flex items-center space-x-2">

                        <span>Registrar Pagamento</span>
                    </a>

                @else

                    <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg flex items-center">
                        Fatura Paga
                    </span>

                @endif

                {{-- Eliminar --}}
                <form action="{{ route('invoices.destroy', $invoice) }}"
                      method="POST"
                      onsubmit="return confirm('Eliminar esta fatura?')">

                    @csrf
                    @method('DELETE')

                    <button class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                        Eliminar
                    </button>

                </form>

            </div>

        </div>
    </x-slot>

    {{-- GRID PRINCIPAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- INFORMAÇÕES --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

            <h2 class="text-lg font-semibold mb-4">
                Informações da Fatura
            </h2>

            <div class="space-y-3">

                <div>
                    <p class="text-sm text-gray-500">Estudante</p>
                    <p class="font-medium">
                        {{ $invoice->student->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Emissão</p>
                    <p class="font-medium">
                        {{ $invoice->issue_date->format('d/m/Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Vencimento</p>
                    <p class="font-medium">
                        {{ $invoice->due_date->format('d/m/Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Status</p>

                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' :
                           ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' :
                           'bg-yellow-100 text-yellow-800') }}">

                        {{ ucfirst($invoice->status) }}

                    </span>

                </div>

            </div>

        </div>

        {{-- DESCRIÇÃO --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

            <h2 class="text-lg font-semibold mb-4">
                Descrição
            </h2>

            <p class="text-gray-700">
                {{ $invoice->description }}
            </p>

        </div>

        {{-- RESUMO FINANCEIRO --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

            <h2 class="text-lg font-semibold mb-4">
                Resumo Financeiro
            </h2>

            <div class="space-y-3">

                <div>
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="font-bold text-lg">
                        Kz {{ number_format($invoice->total_amount, 2, ',', '.') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Pago</p>
                    <p class="font-medium text-green-600">
                        Kz {{ number_format($invoice->amount_paid, 2, ',', '.') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Saldo</p>
                    <p class="font-bold text-red-600">
                        Kz {{ number_format($invoice->balance, 2, ',', '.') }}
                    </p>
                </div>

            </div>

        </div>

    </div>

    {{-- ========================= --}}
    {{-- ITENS DA FATURA (NOVIDADE) --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-lg font-semibold">
                Itens da Fatura
            </h2>

            <div class="text-sm text-gray-500">
                Detalhamento completo
            </div>

        </div>

        <div class="space-y-4">

            @forelse($invoice->items as $item)

                <div class="flex justify-between items-center border-b pb-3">

                    <div>

                        <div class="font-medium text-gray-900">
                            {{ $item->description }}
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ ucfirst($item->type) }}
                        </div>

                    </div>

                    <div class="font-bold text-school-primary">
                        Kz {{ number_format($item->amount, 2, ',', '.') }}
                    </div>

                </div>

            @empty

                <p class="text-gray-500">
                    Nenhum item encontrado nesta fatura.
                </p>

            @endforelse

        </div>

    </div>

    {{-- ========================= --}}
    {{-- PAGAMENTOS --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">

        <div class="flex justify-between items-center mb-4">

            <h2 class="text-lg font-semibold">
                Pagamentos
            </h2>

            @if($invoice->balance > 0)

                <a href="{{ route('invoices.payments.create', $invoice) }}"
                   class="bg-school-primary text-white px-4 py-2 rounded-lg text-sm">

                    Adicionar Pagamento

                </a>

            @endif

        </div>

        <div class="space-y-3">

            @forelse($invoice->payments as $payment)

                <div class="flex justify-between items-center p-3 border rounded-lg">

                    <div>

                        <p class="font-medium">
                            Kz {{ number_format($payment->amount, 2, ',', '.') }}
                        </p>

                        <p class="text-sm text-gray-500">
                            {{ $payment->payment_date->format('d/m/Y') }}
                        </p>

                    </div>

                    <div class="text-right text-sm text-gray-500">

                        {{ ucfirst($payment->payment_method) }}

                        <br>

                        {{ $payment->reference ?? 'N/A' }}

                    </div>

                </div>

            @empty

                <p class="text-gray-500">
                    Nenhum pagamento registrado.
                </p>

            @endforelse

        </div>

    </div>

    {{-- VOLTAR --}}
    <div class="mt-6">

        <a href="{{ route('invoices.index') }}"
           class="text-sm text-gray-600 hover:text-gray-900">

            ← Voltar à lista

        </a>

    </div>

</x-app-layout>