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

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Contrato;
use App\Models\ContratoItem;

class ContratoTest extends TestCase
{
    /** @test */
    public function accessor_total_mensal_soma_itens_regra_5()
    {
        $contrato = new Contrato();
        $item1 = new ContratoItem(['quantidade' => 2, 'valor_unitario' => 100]);
        $item2 = new ContratoItem(['quantidade' => 1, 'valor_unitario' => 200]);

        $contrato->setRelation('itens', collect([$item1, $item2]));

        $this->assertEquals(400.0, $contrato->total_mensal);
    }

    /** @test */
    public function contrato_inicia_como_ativo()
    {
        $contrato = new Contrato();

        $this->assertEquals('Ativo', $contrato->status);
        $this->assertFalse($contrato->isCancelado());
    }
}
