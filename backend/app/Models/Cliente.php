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
 *   schema="Cliente",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="nome", type="string", example="ACME LTDA"),
 *   @OA\Property(property="cpf_cnpj", type="string", example="11222333000181"),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="status", type="string", enum={"Ativo","Inativo"}),
 *   @OA\Property(property="contratos_count", type="integer")
 * )
 * @OA\Schema(schema="ClienteStore", required={"nome","cpf_cnpj","email"},
 *   @OA\Property(property="nome", type="string"),
 *   @OA\Property(property="cpf_cnpj", type="string"),
 *   @OA\Property(property="email", type="string")
 * )
 * @OA\Schema(schema="ClienteUpdate",
 *   @OA\Property(property="nome", type="string"),
 *   @OA\Property(property="status", type="string", enum={"Ativo","Inativo"})
 * )
 */
class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = ['nome', 'cpf_cnpj', 'email', 'status'];

    protected $attributes = [
        'status' => 'Ativo',
    ];

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }

    public function isAtivo(): bool
    {
        return $this->status === 'Ativo';
    }
}
