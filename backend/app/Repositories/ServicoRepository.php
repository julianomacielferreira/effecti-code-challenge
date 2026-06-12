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

use App\Models\Servico;
use DomainException;

class ServicoRepository
{
    public function paginate(array $fields)
    {
        return Servico::when($fields['search'] ?? null, fn($query, $search) =>
        $query->where('nome', 'like', "%$search%"))
            ->orderBy('nome')
            ->paginate(15);
    }

    public function find(int $id): Servico
    {
        return Servico::findOrFail($id);
    }

    public function create(array $data): Servico
    {
        return Servico::create($data);
    }

    public function update(Servico $servico, array $data): Servico
    {
        $servico->update($data);

        return $servico;
    }

    public function delete(Servico $servico): bool
    {
        if ($servico->contratoItens()->exists()) {
            throw new DomainException('Serviço está vinculado a contratos e não pode ser excluído.');
        }

        return (bool) $servico->delete();
    }
}
