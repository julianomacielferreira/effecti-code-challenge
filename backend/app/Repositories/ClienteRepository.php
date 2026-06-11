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

namespace App\Repositories;

use App\Models\Cliente;
use App\Models\Contrato;

use DomainException;

class ClienteRepository
{
    public function paginate(array $fields)
    {
        return Cliente::query()
            ->when($fields['status'] ?? null, fn($query, $search) => $query->where('status', $search))
            ->when($fields['search'] ?? null, fn($query, $search) => $query->where(fn($w) => $w
                ->where('nome', 'like', "%$search%")
                ->orWhere('cpf_cnpj', 'like', "%$search%")))
            ->withCount('contratos')
            ->orderBy('nome')
            ->paginate(15);
    }

    public function findByCpfCnpj(string $cpfCnpj): ?Cliente
    {
        $cpf = preg_replace('/\D/', '', $cpfCnpj);

        return Cliente::where('cpf_cnpj', $cpf)->first();
    }

    public function find(int $id): Cliente
    {
        return Cliente::withCount('contratos')->findOrFail($id);
    }

    public function create(array $data): Cliente
    {
        $data['cpf_cnpj'] = preg_replace('/\D/', '', $data['cpf_cnpj'] ?? '');

        return Cliente::create($data);
    }

    public function update(Cliente $cliente, array $data): Cliente
    {
        $cliente->update($data);

        return $cliente;
    }

    public function delete(Cliente $cliente): bool
    {
        if ($cliente->contratos()->where('status', 'Ativo')->exists()) {
            throw new DomainException('Cliente possui contratos ativos');
        }

        return (bool) $cliente->delete();
    }

    public function hasContratos(int $id): bool
    {
        return Contrato::where('cliente_id', $id)->exists();
    }
}
