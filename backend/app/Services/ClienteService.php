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

namespace App\Services;

use App\Repositories\ClienteRepository;
use DomainException;

class ClienteService
{
    private $repository;

    public function __construct(ClienteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listar(array $f)
    {
        return $this->repository->paginate($f);
    }
    public function buscar(int $id)
    {
        return $this->repository->find($id);
    }
    public function criar(array $d)
    {
        return $this->repository->create($d);
    }

    public function atualizar(int $id, array $d)
    {
        $cliente = $this->repository->find($id);

        return $this->repository->update($cliente, $d);
    }

    // Regra: não excluir com contrato
    public function excluir(int $id): void
    {
        if ($this->repository->hasContratos($id)) {
            throw new DomainException('Cliente possui contratos e não pode ser excluído.');
        }

        $cliente = $this->repository->find($id);

        $this->repository->delete($cliente);
    }
}
