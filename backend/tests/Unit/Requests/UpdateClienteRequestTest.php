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
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Rules\CpfCnpj;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;

class UpdateClienteRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function rules_tem_cpfcnpj_e_unique_ignore()
    {
        $request = $this->makeRequest(5);
        $rules = $request->rules();

        // ['sometimes','string', CpfCnpj, Unique]
        $this->assertEquals('sometimes', $rules['cpf_cnpj'][0]);
        $this->assertEquals('string', $rules['cpf_cnpj'][1]);
        $this->assertInstanceOf(CpfCnpj::class, $rules['cpf_cnpj'][2]);
        $this->assertInstanceOf(Unique::class, $rules['cpf_cnpj'][3]);
    }

    /** @test */
    public function prepare_limpa_mascara()
    {
        $request = UpdateClienteRequest::create('/', 'PUT', ['cpf_cnpj' => '11.222.333/0001-81']);
        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('11222333000181', $request->input('cpf_cnpj'));
    }

    /** @test */
    public function permite_manter_mesmo_cpf_no_update()
    {
        $cliente = Cliente::create([
            'nome' => 'MLOCKS CONSULTORIA',
            'cpf_cnpj' => '52998224725',
            'email' => 'mlocks@email.com'
        ]);

        $request = $this->makeRequest($cliente->id);

        $validator = Validator::make(
            ['cpf_cnpj' => '529.982.247-25'],
            $request->rules()
        );

        $this->assertFalse($validator->fails(), 'Não deveria falhar ao manter o próprio CPF');
    }

    /** @test */
    public function nao_permite_cpf_de_outro_cliente()
    {
        $cliente = Cliente::create(['nome' => 'MLOCKS CONSULTORIA LTDA', 'cpf_cnpj' => '52998224725', 'email' => 'mlocks_ltda@email.com']);

        Cliente::create(['nome' => 'MLOCKS CONSULTORIA', 'cpf_cnpj' => '11222333000181', 'email' => 'mlocks@email.com']);

        $request = $this->makeRequest($cliente->id);

        $validator = Validator::make(
            ['cpf_cnpj' => preg_replace('/\D/', '', '11.222.333/0001-81')],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf_cnpj', $validator->errors()->toArray());
    }

    private function makeRequest(int $id): UpdateClienteRequest
    {
        $request = new UpdateClienteRequest();

        // Fake da rota — resolve o "Route is not bound"
        $request->setRouteResolver(function () use ($id) {
            return new class($id) {
                private $id;
                public function __construct($id)
                {
                    $this->id = $id;
                }
                public function parameter($name, $default = null)
                {
                    return $name === 'cliente' ? $this->id : $default;
                }
            };
        });

        return $request;
    }
}
