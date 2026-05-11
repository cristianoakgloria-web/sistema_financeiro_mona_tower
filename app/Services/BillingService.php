<?php

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function processarCobrancaEmMassa()
    {
        /*
        |--------------------------------------------------------------------------
        | Buscar estudantes com serviços
        |--------------------------------------------------------------------------
        */

        $estudantes = Student::with('services')->get();

        $contagem = 0;

        DB::transaction(function () use ($estudantes, &$contagem) {

            foreach ($estudantes as $estudante) {

                $this->gerarFaturaAluno($estudante);

                $contagem++;
            }

        });

        return $contagem;
    }

    /*
    |--------------------------------------------------------------------------
    | Gerar fatura individual
    |--------------------------------------------------------------------------
    */

    public function gerarFaturaAluno($student)
    {
        /*
        |--------------------------------------------------------------------------
        | Criar invoice
        |--------------------------------------------------------------------------
        */

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . Str::upper(Str::random(8)),

            'student_id' => $student->id,

            'due_date' => now()->day(1)->addMonth(),

            'issue_date' => now(),

            'description' => 'Mensalidade de ' . now()->translatedFormat('F Y'),

            'status' => 'pendente',

            'amount_paid' => 0,

            'total_amount' => 0,
        ]);

        $total = 0;

        /*
        |--------------------------------------------------------------------------
        | Mensalidade base
        |--------------------------------------------------------------------------
        */

        $mensalidade = 79000;

        InvoiceItem::create([
            'invoice_id' => $invoice->id,

            'description' => 'Mensalidade Escolar',

            'amount' => $mensalidade,

            'type' => 'mensalidade',
        ]);

        $total += $mensalidade;

        /*
        |--------------------------------------------------------------------------
        | Serviços associados ao estudante
        |--------------------------------------------------------------------------
        */

        foreach ($student->services as $service) {

            // 👇 SÓ serviços mensais entram na cobrança automática
            if ($service->billing_type !== 'monthly') {
                continue;
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $service->name,
                'amount' => $service->price,
                'type' => 'servico',
            ]);

            $total += $service->price;
        }

        /*
        |--------------------------------------------------------------------------
        | Actualizar total da invoice
        |--------------------------------------------------------------------------
        */

        $invoice->update([
            'total_amount' => $total
        ]);

        return $invoice;
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar status da cobrança automática
    |--------------------------------------------------------------------------
    */

    public function isCobrancaAtiva()
    {
        return Configuracao::where(
            'chave',
            'cobranca_massa_status'
        )->value('valor') === '1';
    }

    /*
    |--------------------------------------------------------------------------
    | Activar/desactivar cobrança
    |--------------------------------------------------------------------------
    */

    public function alternarStatusCobranca($status)
    {
        Configuracao::updateOrCreate(
            ['chave' => 'cobranca_massa_status'],
            ['valor' => $status ? '1' : '0']
        );
    }
}