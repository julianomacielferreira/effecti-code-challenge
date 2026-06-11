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

namespace Tests\Unit\Rules;

use Tests\TestCase;
use App\Rules\CpfCnpj;

class CpfCnpjRuleTest extends TestCase
{
    private $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new CpfCnpj();
    }

    /** @test */
    public function aceita_cpf_valido_sem_mascara()
    {
        // 529.982.247-25 é CPF válido público para testes
        $this->assertTrue($this->rule->passes('cpf', '52998224725'));
    }

    /** @test */
    public function aceita_cpf_valido_com_mascara()
    {
        $this->assertTrue($this->rule->passes('cpf', '529.982.247-25'));
    }

    /** @test */
    public function rejeita_cpf_com_dv_errado()
    {
        $this->assertFalse($this->rule->passes('cpf', '52998224724'));
    }

    /** @test */
    public function rejeita_cpf_sequencia_repetida()
    {
        $this->assertFalse($this->rule->passes('cpf', '11111111111'));
    }

    /** @test */
    public function aceita_cnpj_valido()
    {
        // 11.222.333/0001-81
        $this->assertTrue($this->rule->passes('cnpj', '11222333000181'));
    }

    /** @test */
    public function aceita_cnpj_com_mascara()
    {
        $this->assertTrue($this->rule->passes('cnpj', '11.222.333/0001-81'));
    }

    /** @test */
    public function rejeita_cnpj_invalido()
    {
        $this->assertFalse($this->rule->passes('cnpj', '11222333000182'));
    }

    /** @test */
    public function rejeita_tamanho_invalido()
    {
        $this->assertFalse($this->rule->passes('doc', '12345'));
    }
}
