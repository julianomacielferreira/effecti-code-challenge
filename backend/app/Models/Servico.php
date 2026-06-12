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
 *   schema="Servico",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="nome", type="string", example="Internet Fibra"),
 *   @OA\Property(property="valor_base_mensal", type="number", format="float", example=99.90),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *   schema="ServicoStore",
 *   type="object",
 *   required={"nome","valor_base_mensal"},
 *   @OA\Property(property="nome", type="string", example="Cloud Backup"),
 *   @OA\Property(property="valor_base_mensal", type="number", format="float", example=49.90)
 * )
 * @OA\Schema(
 *   schema="ServicoUpdate",
 *   type="object",
 *   @OA\Property(property="nome", type="string", example="Cloud Backup Pro"),
 *   @OA\Property(property="valor_base_mensal", type="number", format="float", example=59.90)
 * )
 */
class Servico extends Model
{
    use HasFactory;

    protected $table = 'servicos';

    protected $fillable = ['nome', 'valor_base_mensal'];

    protected $casts = [
        'valor_base_mensal' => 'decimal:2',
    ];

    public function itens()
    {
        return $this->hasMany(ContratoItem::class);
    }

    public function contratoItens()
    {
        return $this->hasMany(ContratoItem::class, 'servico_id');
    }
}
