<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InvoiceReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Envia lembretes de faturas a vencer e vencidas';

    public function handle()
    {
        $today = Carbon::today();
        
        // Faturas que vencem em 3 dias
        $upcomingInvoices = Invoice::where('status', 'pending')
            ->whereDate('due_date', $today->copy()->addDays(3))
            ->get();
        
        foreach ($upcomingInvoices as $invoice) {
            if ($invoice->student && $invoice->student->user) {
                $invoice->student->user->notify(new InvoiceReminder($invoice, 'upcoming'));
                $this->info("Lembrete de vencimento enviado para fatura #{$invoice->id}");
            }
        }
        
        // Faturas vencidas
        $overdueInvoices = Invoice::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->get();
        
        foreach ($overdueInvoices as $invoice) {
            if ($invoice->student && $invoice->student->user) {
                $invoice->student->user->notify(new InvoiceReminder($invoice, 'overdue'));
                $this->info("Notificação de fatura vencida enviada para fatura #{$invoice->id}");
            }
        }
        
        $this->info('Lembretes enviados com sucesso!');
    }
}