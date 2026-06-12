<?php
/*
 * The MIT License
 *
 * Copyright 2026 Juliano Maciel.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="Contrato",
 *     type="object",
 *     title="Contrato",
 *     required={"cliente_id", "data_inicio", "status"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="cliente_id",
 *         type="integer",
 *         description="ID do cliente",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="data_inicio",
 *         type="string",
 *         format="date",
 *         example="2024-01-01"
 *     ),
 *     @OA\Property(
 *         property="data_termino",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         example="2024-12-31"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"Ativo", "Cancelado"},
 *         example="Ativo"
 *     ),
 *     @OA\Property(
 *         property="valor_total",
 *         type="number",
 *         format="float",
 *         description="Soma dos itens (accessor)",
 *         example=1500.00
 *     ),
 *     @OA\Property(
 *         property="itens",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ContratoItem")
 *     )
 * )
 */
class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contratos';

    protected $fillable = ['cliente_id', 'data_inicio', 'data_termino', 'status'];

    protected $casts = [
        'data_inicio' => 'date',
        'data_termino' => 'date',
    ];

    protected $attributes = [
        'status' => 'Ativo',
    ];

    protected $appends = ['valor_total'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itens()
    {
        return $this->hasMany(ContratoItem::class);
    }

    /**
     * Regra 5 - CÁLCULO DINÂMICO
     * total_mensal = somatório de (quantidade * valor_unitario)
     * Se a relação já foi carregada (teste), usa collection.
     * Senão, faz SUM no banco (evita N+1, ou seja, uma query para cada contrato).
     */
    public function getTotalMensalAttribute(): float
    {
        if ($this->relationLoaded('itens')) {

            $total = $this->itens->sum(function ($item) {
                return (float) bcmul((string)$item->valor_unitario, (string)$item->quantidade, 2);
            });

            return round($total, 2);
        }

        return (float) $this->itens()
            ->selectRaw('COALESCE(SUM(quantidade * valor_unitario),0) as total')
            ->value('total');
    }

    public function getValorTotalAttribute()
    {
        return $this->itens->sum(function ($i) {
            return $i->quantidade * $i->valor_unitario;
        });
    }

    public function isCancelado(): bool
    {
        return $this->status === 'Cancelado';
    }
}
