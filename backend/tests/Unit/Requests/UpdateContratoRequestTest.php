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
use App\Models\Cliente;
use App\Models\Contrato;
use App\Http\Requests\UpdateContratoRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateContratoRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aceita_patch_vazio()
    {
        $validator = $this->validator([]);

        $this->assertFalse(
            $validator->fails()
        );
    }

    /** @test */
    public function rejeita_cliente_inexistente()
    {
        $validator = $this->validator(['cliente_id' => 99999]);

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey('cliente_id', $validator->errors()->toArray());
    }

    /** @test */
    public function aceita_cliente_existente()
    {
        $cliente = Cliente::factory()->create();

        $validator = $this->validator(['cliente_id' => $cliente->id]);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function rejeita_status_invalido()
    {
        $validator = $this->validator(['status' => 'Pausado']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function aceita_status_ativo()
    {
        $validator = $this->validator(['status' => 'Ativo']);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function aceita_status_cancelado()
    {
        $validator = $this->validator(['status' => 'Cancelado']);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function rejeita_data_termino_menor_que_inicio()
    {
        $validator = $this->validator(['data_inicio' => '2025-01-10', 'data_termino' => '2025-01-05']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('data_termino', $validator->errors()->toArray());
    }

    /** @test */
    public function aceita_data_termino_igual_inicio()
    {
        $validator = $this->validator(['data_inicio' => '2025-01-10', 'data_termino' => '2025-01-10']);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function aceita_data_termino_maior_que_inicio()
    {
        $validator = $this->validator(['data_inicio' => '2025-01-10', 'data_termino' => '2025-01-15']);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function nao_permite_editar_contrato_cancelado()
    {
        $cliente = Cliente::factory()->create();

        $contrato = Contrato::factory()->create(['cliente_id' => $cliente->id, 'status' => 'Cancelado']);

        $validator = $this->validator(['status' => 'Ativo'], $contrato->id);

        $validator->fails();

        $this->assertArrayHasKey('contrato', $validator->errors()->toArray());
        $this->assertEquals('Contrato cancelado não pode ser editado.', $validator->errors()->first('contrato'));
    }

    /** @test */
    public function permite_editar_contrato_ativo()
    {
        $cliente = Cliente::factory()->create();

        $contrato = Contrato::factory()->create(['cliente_id' => $cliente->id, 'status' => 'Ativo']);

        $validator = $this->validator(['status' => 'Cancelado'], $contrato->id);

        $this->assertFalse($validator->fails());
    }

    private function makeRequest(?int $contratoId = null): UpdateContratoRequest
    {
        $request = new UpdateContratoRequest();

        if ($contratoId) {

            $request->setRouteResolver(function () use ($contratoId) {

                return new class($contratoId)
                {
                    private int $id;

                    public function __construct(int $id)
                    {
                        $this->id = $id;
                    }

                    public function parameter(string $name, $default = null)
                    {
                        if ($name === 'contrato') {
                            return $this->id;
                        }

                        if ($name === 'id') {
                            return $this->id;
                        }

                        return $default;
                    }
                };
            });
        }

        return $request;
    }

    private function validator(array $data, ?int $contratoId = null)
    {
        $request = $this->makeRequest($contratoId);

        $validator = Validator::make($data, $request->rules(), $request->messages());

        $request->withValidator($validator);

        return $validator;
    }
}
