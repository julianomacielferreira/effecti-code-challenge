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
use App\Services\ServicoService;
use App\Repositories\ServicoRepository;
use App\Models\Servico;
use Mockery;
use DomainException;

class ServicoServiceTest extends TestCase
{
    /** @test */
    public function listar_delega_para_repository_paginate()
    {
        $repository = Mockery::mock(ServicoRepository::class);
        $repository->shouldReceive('paginate')->once()->with(['search' => 'Oficina'])->andReturn('paginado');

        $service = $this->makeService($repository);
        $this->assertEquals('paginado', $service->listar(['search' => 'Oficina']));
    }

    /** @test */
    public function buscar_delega_para_repository_find()
    {
        $repository = Mockery::mock(ServicoRepository::class);
        $servico = new Servico(['id' => 1]);
        
        $repository->shouldReceive('find')->once()->with(1)->andReturn($servico);

        $service = $this->makeService($repository);
        $this->assertSame($servico, $service->buscar(1));
    }

    /** @test */
    public function criar_delega_para_repository_create()
    {
        $repository = Mockery::mock(ServicoRepository::class);
        $data = ['nome' => 'Oficina de Bikes', 'valor_base_mensal' => 10];
        $servico = new Servico($data);

        $repository->shouldReceive('create')->once()->with($data)->andReturn($servico);

        $service = $this->makeService($repository);
        $this->assertSame($servico, $service->criar($data));
    }

    /** @test */
    public function atualizar_busca_e_depois_atualiza()
    {
        $repository = Mockery::mock(ServicoRepository::class);
        $servico = new Servico(['id' => 1, 'nome' => 'Oficina Antiga']);
        $dados = ['nome' => 'Oficina Nova'];

        $repository->shouldReceive('find')->once()->with(1)->andReturn($servico);
        $repository->shouldReceive('update')->once()->with($servico, $dados)->andReturn($servico);

        $service = $this->makeService($repository);
        $result = $service->atualizar(1, $dados);

        $this->assertSame($servico, $result);
    }

    /** @test */
    public function excluir_busca_e_depois_deleta()
    {
        $repository = Mockery::mock(ServicoRepository::class);
        $servico = new Servico(['id' => 1]);

        $repository->shouldReceive('find')->once()->with(1)->andReturn($servico);
        $repository->shouldReceive('delete')->once()->with($servico)->andReturn(true);

        $service = $this->makeService($repository);
        $this->assertTrue($service->excluir(1));
    }

    /** @test */
    public function excluir_propaga_excecao_de_dominio()
    {
        $repository = Mockery::mock(ServicoRepository::class);
        $servico = new Servico(['id' => 1]);

        $repository->shouldReceive('find')->andReturn($servico);
        $repository->shouldReceive('delete')->andThrow(new DomainException('em uso'));

        $this->expectException(DomainException::class);

        $service = $this->makeService($repository);
        $service->excluir(1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeService($repositoryMock)
    {
        return new ServicoService($repositoryMock);
    }
}
