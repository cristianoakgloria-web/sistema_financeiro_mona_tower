<?php

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Student;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function processarCobrancaEmMassa()
    {
        // Busca todos os estudantes (ou use where('status', 'activo') se já corrigiu a coluna)
        $estudantes = Student::all();
        $contagem = 0;

        DB::transaction(function () use ($estudantes, &$contagem) {
            foreach ($estudantes as $estudante) {
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . Str::upper(Str::random(8)),
                    'student_id'     => $estudante->id,
                    'due_date'       => now()->day(1)->addMonth(), // Vencimento: dia 1 do próximo mês
                    'issue_date'     => now(),
                    'total_amount'   => 79000,
                    'description'    => 'Mensalidade de ' . now()->translatedFormat('F Y'),
                    'status'         => 'pendente',
                    'amount_paid'    => 0,
                ]);
                $contagem++;
            }
        });

        return $contagem;
    }

    public function isCobrancaAtiva()
    {
        return Configuracao::where('chave', 'cobranca_massa_status')->value('valor') === '1';
    }

    public function alternarStatusCobranca($status)
    {
        Configuracao::updateOrCreate(
            ['chave' => 'cobranca_massa_status'],
            ['valor' => $status ? '1' : '0']
        );
    }
}