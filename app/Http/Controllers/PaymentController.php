<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Traits\HasAuditLog;
use App\Notifications\PaymentConfirmed;
use App\Notifications\PaymentReceived;
use App\Notifications\PaymentRejected;
use Illuminate\Support\Facades\Notification;

class PaymentController extends Controller
{
    use HasAuditLog;

    public function confirm($id)
    {
        $payment = Payment::findOrFail($id);

        $old = $payment->getOriginal();

        $payment->update([
            'status' => 'confirmed'
        ]);

       // $this->logActivity($payment, 'payment_confirmed', $old, $payment->getChanges());

        $this->updateInvoiceStatus($payment->invoice);

        // Notifica os administradores e equipe financeira sobre a confirmação do pagamento
        $users = User::whereIn('role', ['admin', 'financeiro'])->get();
        Notification::send($users, new PaymentConfirmed($payment));
        // Notifica o estudante e o responsável sobre a confirmação do pagamento

        return back()->with('success', 'Pagamento confirmado com sucesso.');
    }

    public function reject($id)
    {
        // 1. Removemos a injeção do Invoice da assinatura
        $payment = Payment::findOrFail($id);

        // 2. Captura o estado ANTES da alteração
        $old = $payment->getOriginal();

        $payment->update([
            'status' => 'rejected'
        ]);

        // 3. Regista a auditoria de forma limpa e dinâmica
        $this->logActivity(
            'pagamento_rejeitado',
            $payment,
            $old,
            $payment->getChanges() // Captura apenas o que foi alterado (o status)
        );

        // 4. Atualiza o estado da fatura (ex: volta a ficar 'pendente' ou 'parcial')
        $this->updateInvoiceStatus($payment->invoice);

        // 5. Notifica os utilizadores com perfil de gestão
        $users = User::whereIn('role', ['admin', 'secretaria'])->get();
        
        // Passamos $payment->invoice diretamente para a notificação
        Notification::send($users, new PaymentRejected($payment, $payment->invoice));

        return back()->with('success', 'Pagamento rejeitado com sucesso.');
    }

    public function index()
    {
        $payments = Payment::with(['invoice.student'])->latest()->paginate(6);

        return view('payments.index', compact('payments'));
    }

    public function create(Invoice $invoice)
    {
        $invoice->load(['student']);
        
        if (!$invoice->student) {
            return redirect()->route('invoices.edit', ['invoice' => $invoice->id])
                ->with('error', 'Esta fatura não tem um estudante associado.');
        }

        return view('payments.create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice, Payment $payment)
    {
        if (!$invoice->student) {
            return redirect()->route('invoices.edit', ['invoice' => $invoice->id])
                ->with('error', 'Esta fatura não tem um estudante associado.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,card,mobile_money',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'status' => 'pending', // Garante que nasce pendente
        ]);

        $this->updateInvoiceStatus($invoice);

        // Notificar todos os Administradores e equipe financeira sobre o novo pagamento
        //$payment = Payment::findOrFail($id);
        $users = User::whereIn('role', ['admin', 'financeiro'])->get();
        Notification::send($users, new PaymentReceived($payment, $invoice));

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pagamento registado. Aguarda confirmação.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['invoice.student.guardian']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $payment->load(['invoice.student']);
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $invoice = $payment->invoice;
        // Permite editar considerando o saldo corretamente sem contar os rejeitados
        $maxAmount = $invoice->total_amount - ($invoice->payments()->where('status', '!=', 'rejected')->sum('amount') - $payment->amount);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $maxAmount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,card,mobile_money',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update($request->all());
        $this->updateInvoiceStatus($invoice);

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Pagamento atualizado com sucesso.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $this->updateInvoiceStatus($invoice);

        return redirect()->route('payments.index')
            ->with('success', 'Pagamento eliminado com sucesso.');
    }

    public function createFullPayment(Invoice $invoice)
    {
        $invoice->load(['student']);

        if (!$invoice->student) {
            return redirect()->route('invoices.edit', ['invoice' => $invoice->id])
                ->with('error', 'Esta fatura não tem um estudante associado.');
        }

        if ($invoice->balance <= 0) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Esta fatura já está totalmente paga.');
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->balance,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'reference' => 'Pagamento total automático',
            'notes' => 'Pagamento realizado automaticamente através do sistema.',
            'status' => 'pending', // Garante que nasce pendente
        ]);

        $this->updateInvoiceStatus($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pagamento total registrado. Aguarda confirmação.');
    }

    private function updateInvoiceStatus(Invoice $invoice)
    {
        // Apenas pagamentos confirmados abatem o saldo real
        $totalConfirmed = $invoice->payments()->where('status', 'confirmed')->sum('amount');
        $invoice->amount_paid = $totalConfirmed;

        // Verifica se há pagamentos a aguardar decisão
        $hasPending = $invoice->payments()->where('status', 'pending')->exists();

        if ($totalConfirmed >= $invoice->total_amount) {
            $invoice->status = 'paid';
        } elseif ($hasPending) {
            $invoice->status = 'em_validacao';
        } elseif ($totalConfirmed > 0) {
            $invoice->status = 'partial';
        } elseif ($invoice->due_date < now()) {
            $invoice->status = 'overdue';
        } else {
            $invoice->status = 'pending';
        }
        
        $invoice->save();
    }

    protected function logActivity($action, $model, $oldValues = null, $newValues = null)
    {
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}