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
use App\Rules\Contrato\DescontoFidelidadeRule;
use App\Models\Contrato;
use App\Models\Cliente;
use Carbon\Carbon;

class DescontoFidelidadeRuleTest extends TestCase
{
    /** @test */
    public function aplica_3_porcento_para_cliente_antigo()
    {
        $regra = new DescontoFidelidadeRule();
        $cliente = new Cliente();
        $cliente->created_at = Carbon::now()->subYears(2);

        $contrato = new Contrato();
        $contrato->setRelation('cliente', $cliente);

        $this->assertEquals(970, $regra->aplicar($contrato, 1000));
    }

    /** @test */
    public function nao_aplica_para_cliente_novo()
    {
        $regra = new DescontoFidelidadeRule();
        $cliente = new Cliente();
        $cliente->created_at = Carbon::now()->subMonths(6);

        $contrato = new Contrato();
        $contrato->setRelation('cliente', $cliente);

        $this->assertEquals(1000, $regra->aplicar($contrato, 1000));
    }
}
