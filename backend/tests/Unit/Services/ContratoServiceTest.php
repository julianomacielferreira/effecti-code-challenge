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
use App\Services\ContratoService;
use App\Repositories\ContratoRepository;
use App\Services\CalculadoraDeContrato;
use App\Models\Contrato;
use Mockery;

class ContratoServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function listar_delega_para_paginate()
    {
        $repository = Mockery::mock(ContratoRepository::class);
        $repository->shouldReceive('paginate')->with(['status' => 'Ativo'])->andReturn('paginado');

        $service = new ContratoService($repository, Mockery::mock(CalculadoraDeContrato::class));
        $this->assertEquals('paginado', $service->listar(['status' => 'Ativo']));
    }

    /** @test */
    public function buscar_delega_para_find_e_adiciona_totais()
    {
        $contrato = new Contrato(['id' => 1]);
        $repository = Mockery::mock(ContratoRepository::class);
        $repository->shouldReceive('find')->with(1)->andReturn($contrato);

        $calculadora = Mockery::mock(CalculadoraDeContrato::class);
        $calculadora->shouldReceive('calcular')->with($contrato)->andReturn(['total' => 100]);

        $service = new ContratoService($repository, $calculadora);
        $result = $service->buscar(1);

        $this->assertEquals(['total' => 100], $result->totais);
    }

    /** @test */
    public function criar_delega_para_create()
    {
        $repository = Mockery::mock(ContratoRepository::class);
        $repository->shouldReceive('create')->with(['cliente_id' => 1])->andReturn(new Contrato());

        $service = new ContratoService($repository, Mockery::mock(CalculadoraDeContrato::class));
        $this->assertInstanceOf(Contrato::class, $service->criar(['cliente_id' => 1]));
    }

    /** @test */
    public function calcular_total_delega_para_calculadora()
    {
        $contrato = new Contrato();
        $repository = Mockery::mock(ContratoRepository::class);
        $repository->shouldReceive('find')->with(5)->andReturn($contrato);

        $calculadora = Mockery::mock(CalculadoraDeContrato::class);
        $calculadora->shouldReceive('calcular')->andReturn(['total' => 500]);

        $service = new ContratoService($repository, $calculadora);
        $this->assertEquals(['total' => 500], $service->calcularTotal(5));
    }
}
