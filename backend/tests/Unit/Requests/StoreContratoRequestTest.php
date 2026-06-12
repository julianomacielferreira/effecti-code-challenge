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
use App\Http\Requests\StoreContratoRequest;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class StoreContratoRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aceita_dados_validos()
    {
        $cliente = Cliente::factory()->create();

        $validator = $this->validator([
            'cliente_id' => $cliente->id,
            'data_inicio' => '2024-01-01',
            'data_termino' => '2024-12-31',
            'status' => 'Ativo'
        ]);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function data_inicio_obrigatoria()
    {
        $cliente = Cliente::factory()->create();

        $validator = $this->validator([
            'cliente_id' => $cliente->id
        ]);

        $this->assertTrue($validator->fails());
        $this->assertEquals('A data de início é obrigatória.', $validator->errors()->first('data_inicio'));
    }

    /** @test */
    public function data_termino_deve_ser_maior_que_inicio()
    {
        $cliente = Cliente::factory()->create();

        $validator = $this->validator([
            'cliente_id' => $cliente->id,
            'data_inicio' => '2024-01-10',
            'data_termino' => '2024-01-05'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            'Data de término deve ser maior ou igual à data de início.',
            $validator->errors()->first('data_termino')
        );
    }

    private function validator(array $data)
    {
        $request = new StoreContratoRequest();

        return Validator::make($data, $request->rules(), $request->messages());
    }
}
