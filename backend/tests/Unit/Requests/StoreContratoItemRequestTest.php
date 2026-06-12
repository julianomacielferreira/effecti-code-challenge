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
use App\Http\Requests\StoreContratoItemRequest;
use App\Models\Contrato;
use App\Models\Servico;
use App\Models\ContratoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class StoreContratoItemRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function servico_obrigatorio()
    {
        $contrato = Contrato::factory()->create();
        $validator = $this->validator([], $contrato->id);

        $this->assertTrue($validator->fails());
        $this->assertEquals('O serviço é obrigatório.', $validator->errors()->first('servico_id'));
    }

    /** @test */
    public function servico_deve_existir()
    {
        $contrato = Contrato::factory()->create();

        $validator = $this->validator([
            'servico_id' => 999,
            'quantidade' => 1,
            'valor_unitario' => 10
        ], $contrato->id);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Serviço não encontrado.', $validator->errors()->first('servico_id'));
    }

    /** @test */
    public function quantidade_deve_ser_positiva()
    {
        $contrato = Contrato::factory()->create();

        $servico = Servico::factory()->create();

        $validator = $this->validator([
            'servico_id' => $servico->id,
            'quantidade' => 0,
            'valor_unitario' => 10
        ], $contrato->id);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Quantidade deve ser maior que zero.', $validator->errors()->first('quantidade'));
    }

    /** @test */
    public function valor_unitario_deve_ser_positivo()
    {
        $contrato = Contrato::factory()->create();

        $servico = Servico::factory()->create();

        $validator = $this->validator([
            'servico_id' => $servico->id,
            'quantidade' => 1,
            'valor_unitario' => 0
        ], $contrato->id);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Valor unitário deve ser maior que zero.', $validator->errors()->first('valor_unitario'));
    }

    /** @test */
    public function nao_permite_servico_duplicado_no_mesmo_contrato()
    {
        $contrato = Contrato::factory()->create();
        $servico = Servico::factory()->create();
        ContratoItem::factory()->create([
            'contrato_id' => $contrato->id,
            'servico_id' => $servico->id
        ]);

        $validator = $this->validator([
            'servico_id' => $servico->id,
            'quantidade' => 1,
            'valor_unitario' => 10
        ], $contrato->id);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Este serviço já foi adicionado ao contrato.', $validator->errors()->first('servico_id'));
    }

    /** @test */
    public function aceita_dados_validos()
    {
        $contrato = Contrato::factory()->create();

        $servico = Servico::factory()->create();

        $validator = $this->validator([
            'servico_id' => $servico->id,
            'quantidade' => 5,
            'valor_unitario' => 150.50
        ], $contrato->id);

        $this->assertFalse($validator->fails());
    }

    private function validator(array $data, int $contratoId)
    {
        $request = new StoreContratoItemRequest();

        $request->setRouteResolver(function () use ($contratoId) {

            return new class($contratoId) {
                private int $id;

                public function __construct(int $id)
                {
                    $this->id = $id;
                }

                public function parameter($name)
                {
                    return $this->id;
                }
            };
        });

        return Validator::make($data, $request->rules(), $request->messages());
    }
}
