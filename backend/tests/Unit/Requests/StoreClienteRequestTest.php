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

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\StoreClienteRequest;
use App\Rules\CpfCnpj;

class StoreClienteRequestTest extends TestCase
{
    /** @test */
    public function rules_tem_cpfcnpj_e_unique()
    {
        $request = new StoreClienteRequest();
        $rules = $request->rules();

        // Garante que a Rule esta presente
        $hasRule = false;
        foreach ($rules['cpf_cnpj'] as $rule) {
            if ($rule instanceof \App\Rules\CpfCnpj) $hasRule = true;
        }
        $this->assertTrue($hasRule, 'CpfCnpj Rule não encontrada');

        // Garante que o unique está presente
        $this->assertContains('unique:clientes,cpf_cnpj', $rules['cpf_cnpj']);
    }

    /** @test */
    public function prepare_limpa_mascara_antes_de_validar()
    {
        $request = StoreClienteRequest::create('/', 'POST', [
            'cpf_cnpj' => '529.982.247-25'
        ]);

        // Chama o método protegido
        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('52998224725', $request->input('cpf_cnpj'));
    }
}
