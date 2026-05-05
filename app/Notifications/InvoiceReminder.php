<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceReminder extends Notification
{
    use Queueable;

    public function __construct(public $invoice, public $type)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $today = now()->startOfDay();
        $dueDate = \Carbon\Carbon::parse($this->invoice->due_date)->startOfDay();
        $daysOverdue = $dueDate->diffInDays($today, false);
        
        if ($this->type == 'upcoming_3days') {
            $title = 'Lembrete de vencimento';
            $message = "A fatura {$this->invoice->invoice_number} vence em 3 dias. Valor: " . number_format($this->invoice->total_amount, 2, ',', '.') . " Kz";
        } else {
            $title = 'Fatura vencida';
            if ($daysOverdue <= 0) {
                $message = "A fatura {$this->invoice->invoice_number} vence hoje! Data de vencimento: " . date('d/m/Y', strtotime($this->invoice->due_date)) . ". Valor: " . number_format($this->invoice->total_amount, 2, ',', '.') . " Kz";
            } else {
                $message = "A fatura {$this->invoice->invoice_number} esta vencida ha {$daysOverdue} dia(s). Data de vencimento: " . date('d/m/Y', strtotime($this->invoice->due_date)) . ". Valor: " . number_format($this->invoice->total_amount, 2, ',', '.') . " Kz";
            }
        }

        return [
            'title' => $title,
            'message' => $message,
            'amount' => $this->invoice->total_amount,
            'payment_id' => null,
            'invoice_id' => $this->invoice->id,
            'type' => $this->type,
            'due_date' => $this->invoice->due_date,
            'invoice_number' => $this->invoice->invoice_number,
        ];
    }
}