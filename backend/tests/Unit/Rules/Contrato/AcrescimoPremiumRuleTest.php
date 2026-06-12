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
use App\Rules\Contrato\AcrescimoPremiumRule;
use App\Rules\Contrato\DescontoPorQuantidadeRule;
use App\Services\CalculadoraDeContrato;
use App\Models\Contrato;
use App\Models\ContratoItem;
use App\Models\Servico;
use App\Models\Cliente;

class AcrescimoPremiumRuleTest extends TestCase
{
    private function item(string $nomeServico, int $qtd = 1, float $valor = 100): ContratoItem
    {
        $item = new ContratoItem(['quantidade' => $qtd, 'valor_unitario' => $valor]);
        $item->setRelation('servico', new Servico(['nome' => $nomeServico]));
        return $item;
    }

    /** @test */
    public function aplica_acrescimo_quando_tem_servico_premium()
    {
        $regra = new AcrescimoPremiumRule();
        $contrato = new Contrato();
        $contrato->setRelation('itens', collect([
            $this->item('Plano Premium', 2, 100),
        ]));

        $this->assertEquals(204.00, $regra->aplicar($contrato, 200.00));
    }

    /** @test */
    public function nao_aplica_acrescimo_sem_servico_premium()
    {
        $regra = new AcrescimoPremiumRule();
        $contrato = new Contrato();
        $contrato->setRelation('itens', collect([
            $this->item('Básico', 1, 100),
            $this->item('Standard', 1, 50),
        ]));

        $this->assertEquals(150.00, $regra->aplicar($contrato, 150.00));
    }

    /** @test */
    public function detecta_premium_case_insensitive()
    {
        $regra = new AcrescimoPremiumRule();
        $contrato = new Contrato();
        $contrato->setRelation('itens', collect([
            $this->item('PREMIUM Gold'),
        ]));

        $this->assertEquals(102.00, $regra->aplicar($contrato, 100.00));
    }

    /** @test */
    public function nome_retorna_identificador_correto()
    {
        $this->assertEquals('acrescimo_premium', (new AcrescimoPremiumRule())->nome());
    }

    /** @test */
    public function funciona_no_pipeline_com_outras_regras()
    {
        $contrato = new Contrato();
        $contrato->setRelation('itens', collect([
            $this->item('Serviço Premium', 10, 100), // subtotal 1000
        ]));
        $contrato->setRelation('cliente', new Cliente(['created_at' => now()]));

        $calculadora = new CalculadoraDeContrato([
            new DescontoPorQuantidadeRule(), // 10 itens = -5% => 950
            new AcrescimoPremiumRule(),      // +2% => 969
        ]);

        $totais = $calculadora->calcular($contrato);

        $this->assertEquals(1000.00, $totais['subtotal']);
        $this->assertEquals(969.00, $totais['total']);
        $this->assertContains('desconto_quantidade', $totais['regras_aplicadas']);
        $this->assertContains('acrescimo_premium', $totais['regras_aplicadas']);
    }
}
