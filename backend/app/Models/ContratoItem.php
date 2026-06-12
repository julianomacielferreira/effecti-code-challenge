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
 *     schema="ContratoItem",
 *     type="object",
 *     title="Item do Contrato",
 *     required={"servico_id", "quantidade", "valor_unitario"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="contrato_id", type="integer", example=1),
 *     @OA\Property(property="servico_id", type="integer", example=5),
 *     @OA\Property(
 *         property="quantidade",
 *         type="integer",
 *         minimum=1,
 *         example=10
 *     ),
 *     @OA\Property(
 *         property="valor_unitario",
 *         type="number",
 *         format="float",
 *         example=150.00
 *     )
 * )
 */
class ContratoItem extends Model
{
    use HasFactory;

    protected $table = 'contrato_itens';

    protected $fillable = ['contrato_id', 'servico_id', 'quantidade', 'valor_unitario'];

    protected $casts = [
        'quantidade' => 'integer',
        'valor_unitario' => 'decimal:2',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) bcmul((string) $this->valor_unitario, (string) $this->quantidade, 2);
    }
}
