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

use App\Repositories\ContratoRepository;
use App\Services\CalculadoraDeContrato;

class ContratoService
{
    private ContratoRepository $repository;
    private CalculadoraDeContrato $calculadora;

    public function __construct(ContratoRepository $repository, CalculadoraDeContrato $calculadora)
    {
        $this->repository = $repository;
        $this->calculadora = $calculadora;
    }

    public function listar(array $filtros)
    {
        return $this->repository->paginate($filtros);
    }

    public function buscar(int $id)
    {
        $contrato = $this->repository->find($id);

        $contrato->totais = $this->calculadora->calcular($contrato);

        return $contrato;
    }

    public function criar(array $dados)
    {
        return $this->repository->create($dados);
    }

    public function calcularTotal(int $id): array
    {
        $contrato = $this->repository->find($id);

        return $this->calculadora->calcular($contrato);
    }
}
