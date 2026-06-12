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

namespace Tests\Unit\Rules\Contrato;

use Tests\TestCase;
use App\Rules\Contrato\DescontoPorQuantidadeRule;
use App\Models\Contrato;
use App\Models\ContratoItem;

class DescontoPorQuantidadeRuleTest extends TestCase
{
    private function contratoComItens(int $qtd, float $valor = 100): Contrato
    {
        $contrato = new Contrato();
        $item = new ContratoItem(['quantidade' => $qtd, 'valor_unitario' => $valor]);
        $contrato->setRelation('itens', collect([$item]));
        return $contrato;
    }

    /** @test */
    public function nao_aplica_desconto_abaixo_de_10()
    {
        $regra = new DescontoPorQuantidadeRule();
        $c = $this->contratoComItens(9);

        $this->assertEquals(900, $regra->aplicar($c, 900));
    }

    /** @test */
    public function aplica_5_porcento_para_10_a_19()
    {
        $regra = new DescontoPorQuantidadeRule();
        $c = $this->contratoComItens(10);

        $this->assertEquals(950, $regra->aplicar($c, 1000));
    }

    /** @test */
    public function aplica_10_porcento_para_20_ou_mais()
    {
        $regra = new DescontoPorQuantidadeRule();
        $c = $this->contratoComItens(20);

        $this->assertEquals(900, $regra->aplicar($c, 1000));
    }

    /** @test */
    public function nome_retorna_identificador()
    {
        $this->assertEquals('desconto_quantidade', (new DescontoPorQuantidadeRule())->nome());
    }
}
