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

namespace Tests\Unit\Services\Contrato;

use Tests\TestCase;
use App\Services\CalculadoraDeContrato;
use App\Rules\Contrato\DescontoPorQuantidadeRule;
use App\Rules\Contrato\DescontoFidelidadeRule;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Cliente;
use Carbon\Carbon;

class CalculadoraDeContratoTest extends TestCase
{
    private function contrato(int $quantidade, float $valorUnitario = 100, bool $clienteAntigo = false): Contrato
    {
        $contrato = new Contrato();

        $contrato->setRelation('itens', collect([
            new ContratoItem(['quantidade' => $quantidade, 'valor_unitario' => $valorUnitario])
        ]));

        $cliente = new Cliente();
        $cliente->created_at = $clienteAntigo ? Carbon::now()->subYears(2) : Carbon::now();
        $contrato->setRelation('cliente', $cliente);

        return $contrato;
    }

    /** @test */
    public function calcula_subtotal_sem_regras()
    {
        $calculadora = new CalculadoraDeContrato([]);
        $resultado = $calculadora->calcular($this->contrato(5, 200));

        $this->assertEquals(1000, $resultado['subtotal']);
        $this->assertEquals(1000, $resultado['total']);
        $this->assertEmpty($resultado['regras_aplicadas']);
    }

    /** @test */
    public function aplica_multiplas_regras_em_pipeline()
    {
        $calculadora = new CalculadoraDeContrato([
            new DescontoPorQuantidadeRule(),
            new DescontoFidelidadeRule(),
        ]);

        // 20 itens = 2000, 10% = 1800, fidelidade 3% = 1746
        $resultado = $calculadora->calcular($this->contrato(20, 100, true));

        $this->assertEquals(2000, $resultado['subtotal']);
        $this->assertEquals(1746, $resultado['total']);
        $this->assertEquals(['desconto_quantidade', 'fidelidade'], $resultado['regras_aplicadas']);
    }
}
