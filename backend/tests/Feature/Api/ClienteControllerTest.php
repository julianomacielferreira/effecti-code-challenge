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
use App\Models\Contrato;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function lista_paginado()
    {
        Cliente::factory()->count(20)->create();

        $this->getJson('/api/clientes')->assertOk()->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'per_page',
            'total'
        ]);
    }

    /** @test */
    public function cria_cliente()
    {
        $response = $this->postJson('/api/clientes', [
            'nome' => 'MLOCKS CONSULTORIA',
            'cpf_cnpj' => '52998224725',
            'email' => 'mlocks@email.com'
        ])->assertCreated();

        $this->assertDatabaseHas('clientes', ['cpf_cnpj' => '52998224725']);
    }

    /** @test */
    public function nao_cria_duplicado()
    {
        Cliente::create(['nome' => 'MLOCKS CONSULTORIA', 'cpf_cnpj' => '52998224725', 'email' => 'mlocks@email.com']);

        $this->postJson('/api/clientes', [
            'nome' => 'Oficina',
            'cpf_cnpj' => '529.982.247-25',
            'email' => 'mlocks@email.com'
        ])->assertStatus(422)->assertJson(['message' => 'Este CPF/CNPJ já está cadastrado.']);
    }

    /** @test */
    public function mostra_com_contagem()
    {
        $cliente = Cliente::factory()->create();

        $this->getJson("/api/clientes/{$cliente->id}")->assertOk()->assertJsonFragment(['contratos_count' => 0]);
    }

    /** @test */
    public function atualiza()
    {
        $cliente = Cliente::factory()->create();

        $this->putJson("/api/clientes/{$cliente->id}", ['nome' => 'MLOCKS CONSULTORIA'])->assertOk();

        $this->assertDatabaseHas('clientes', ['nome' => 'MLOCKS CONSULTORIA']);
    }

    /** @test */
    public function nao_exclui_com_contrato()
    {
        $cliente = Cliente::factory()->create();

        Contrato::factory()->create(['cliente_id' => $cliente->id]);

        $this->deleteJson("/api/clientes/{$cliente->id}")
            ->assertStatus(422)
            ->assertJson(['message' => 'Cliente possui contratos e não pode ser excluído.']);
    }

    /** @test */
    public function exclui_sem_contrato()
    {
        $cliente = Cliente::factory()->create();

        $this->deleteJson("/api/clientes/{$cliente->id}")->assertNoContent();
    }
}
