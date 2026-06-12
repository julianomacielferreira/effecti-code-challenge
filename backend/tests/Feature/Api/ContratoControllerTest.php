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
use App\Models\Servico;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContratoControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function lista_paginado()
    {
        Contrato::factory()->count(3)->create();
        $this->getJson('/api/contratos')->assertOk()->assertJsonStructure(['data']);
    }

    /** @test */
    public function cria_contrato()
    {
        $cliente = Cliente::factory()->create();
        $this->postJson('/api/contratos', [
            'cliente_id' => $cliente->id,
            'data_inicio' => '2024-01-01',
            'status' => 'Ativo'
        ])->assertCreated()->assertJsonPath('cliente_id', $cliente->id);
    }

    /** @test */
    public function mostra_com_totais()
    {
        $contrato = Contrato::factory()->hasItens(2)->create();
        $this->getJson("/api/contratos/{$contrato->id}")
            ->assertOk()
            ->assertJsonStructure(['id', 'itens', 'totais', 'valor_total']);
    }

    /** @test */
    public function atualiza_contrato()
    {
        $contrato = Contrato::factory()->create(['status' => 'Ativo']);
        $this->putJson("/api/contratos/{$contrato->id}", ['status' => 'Cancelado'])
            ->assertOk()
            ->assertJsonPath('status', 'Cancelado');
    }

    /** @test */
    public function exclui_contrato()
    {
        $contrato = Contrato::factory()->create();
        $this->deleteJson("/api/contratos/{$contrato->id}")->assertNoContent();
    }

    /** @test */
    public function adiciona_item()
    {
        $contrato = Contrato::factory()->create(['status' => 'Ativo']);
        $servico = Servico::factory()->create();

        $this->postJson("/api/contratos/{$contrato->id}/itens", [
            'servico_id' => $servico->id,
            'quantidade' => 10,
            'valor_unitario' => 100
        ])->assertCreated();
    }

    /** @test */
    public function nao_adiciona_item_em_cancelado()
    {
        $contrato = Contrato::factory()->create(['status' => 'Cancelado']);
        $servico = Servico::factory()->create();

        $this->postJson("/api/contratos/{$contrato->id}/itens", [
            'servico_id' => $servico->id,
            'quantidade' => 1,
            'valor_unitario' => 10
        ])->assertStatus(422)->assertJson(['message' => 'Contrato não está ativo']);
    }
}
