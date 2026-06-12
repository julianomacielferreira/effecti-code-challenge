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

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nao_permite_cadastrar_cpf_duplicado()
    {
        // 1º cadastro (válido)
        Cliente::create([
            'nome' => 'MLOCKS CONSULTORIA',
            'cpf_cnpj' => '52998224725',
            'email' => 'mlocks_consultoria@email.com',
        ]);

        // 2º tentativa com o mesmo CPF, mas com máscara
        $response = $this->postJson('/api/clientes', [
            'nome' => 'Duplicado',
            'cpf_cnpj' => '529.982.247-25', // prepareForValidation vai limpar
            'email' => 'outro@teste.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('cpf_cnpj');
        $this->assertEquals(
            'Este CPF/CNPJ já está cadastrado.',
            $response->json('errors.cpf_cnpj.0')
        );
    }
}
