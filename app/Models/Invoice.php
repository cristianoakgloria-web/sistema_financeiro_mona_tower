<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAuditLog; // 1. Importação correta

class Invoice extends Model
{
    use HasFactory, HasAuditLog; // 2. Uso simplificado dos Traits

    // Removi a duplicação. Apenas um array $fillable com todos os campos.
    protected $fillable = [
        'invoice_number',
        'student_id',
        'due_date',
        'issue_date',
        'status',
        'total_amount',
        'amount_paid',
        'description'
    ];

    protected $casts = [
        'due_date' => 'date',
        'issue_date' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    // --- Relacionamentos ---

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // --- Atributos Dinâmicos ---

    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function isOverdue()
    {
        // Verifica se a data de vencimento passou e se ainda não foi paga
        return $this->due_date < now() && !in_array($this->status, ['paid', 'pago']);
    }

    // --- Ciclo de Vida (Eventos) ---

    /**
     * IMPORTANTE: No Laravel 10/11/12, a recomendação é usar booted() em vez de boot()
     * para evitar conflitos com os construtores internos do Eloquent.
     */
    protected static function booted()
    {
        static::saving(function ($invoice) {
            if ($invoice->isOverdue()) {
                $invoice->status = 'overdue';
            }
        });
    }
}