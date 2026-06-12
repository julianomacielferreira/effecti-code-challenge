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

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ClienteService;
use App\Repositories\ClienteRepository;
use App\Models\Cliente;
use DomainException;
use Mockery;

class ClienteServiceTest extends TestCase
{
    /** @test */
    public function listar_delega_para_paginate()
    {
        $repository = Mockery::mock(ClienteRepository::class);

        $repository->shouldReceive('paginate')->once()->with(['status' => 'Ativo'])->andReturn('paginado');

        $service = $this->makeService($repository);

        $this->assertEquals('paginado', $service->listar(['status' => 'Ativo']));
    }

    /** @test */
    public function buscar_delega_para_find()
    {
        $repository = Mockery::mock(ClienteRepository::class);

        $cliente = new Cliente(['id' => 1]);

        $repository->shouldReceive('find')->once()->with(1)->andReturn($cliente);

        $service = $this->makeService($repository);

        $this->assertSame($cliente, $service->buscar(1));
    }

    /** @test */
    public function criar_delega_para_create()
    {
        $repository = Mockery::mock(ClienteRepository::class);

        $dados = ['nome' => 'Juliano Maciel Ferreira', 'cpf_cnpj' => '123'];

        $cliente = new Cliente($dados);

        $repository->shouldReceive('create')->once()->with($dados)->andReturn($cliente);

        $service = $this->makeService($repository);

        $this->assertSame($cliente, $service->criar($dados));
    }

    /** @test */
    public function atualizar_busca_e_depois_atualiza()
    {
        $repository = Mockery::mock(ClienteRepository::class);

        $cliente = new Cliente(['id' => 5]);

        $dados = ['nome' => 'Juliano'];

        $repository->shouldReceive('find')->once()->with(5)->andReturn($cliente);
        $repository->shouldReceive('update')->once()->with($cliente, $dados)->andReturn($cliente);

        $service = $this->makeService($repository);
        $result = $service->atualizar(5, $dados);

        $this->assertSame($cliente, $result);
    }

    /** @test */
    public function excluir_quando_nao_tem_contrato()
    {
        $repository = Mockery::mock(ClienteRepository::class);

        $cliente = new Cliente(['id' => 2]);

        $repository->shouldReceive('hasContratos')->once()->with(2)->andReturn(false);
        $repository->shouldReceive('find')->once()->with(2)->andReturn($cliente);
        $repository->shouldReceive('delete')->once()->with($cliente)->andReturn(true);

        $service = $this->makeService($repository);
        $service->excluir(2);

        $this->assertTrue(true);
    }

    /** @test */
    public function excluir_lanca_excecao_se_tem_contrato()
    {
        $repository = Mockery::mock(ClienteRepository::class);

        $repository->shouldReceive('hasContratos')->once()->with(3)->andReturn(true);

        $repository->shouldNotReceive('find');
        $repository->shouldNotReceive('delete');

        $service = $this->makeService($repository);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cliente possui contratos');

        $service->excluir(3);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeService($repository)
    {
        return new ClienteService($repository);
    }
}
