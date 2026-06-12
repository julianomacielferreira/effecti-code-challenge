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

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\ContratoRepository;
use App\Models\Contrato;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContratoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ContratoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ContratoRepository();
    }

    /** @test */
    public function find_retorna_com_relacionamentos()
    {
        $cliente = Cliente::factory()->create();
        $contrato = Contrato::factory()->create(['cliente_id' => $cliente->id]);

        $found = $this->repository->find($contrato->id);

        $this->assertTrue($found->relationLoaded('cliente'));
        $this->assertTrue($found->relationLoaded('itens'));
    }

    /** @test */
    public function find_lanca_excecao_se_nao_existir()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->repository->find(999);
    }

    /** @test */
    public function create_persiste_dados()
    {
        $cliente = Cliente::factory()->create();

        $dados = [
            'cliente_id' => $cliente->id,
            'data_inicio' => '2024-01-01',
            'status' => 'Ativo'
        ];

        $contrato = $this->repository->create($dados);

        $this->assertDatabaseHas('contratos', ['id' => $contrato->id, 'status' => 'Ativo']);
    }

    /** @test */
    public function paginate_filtra_por_status()
    {
        Cliente::factory()->create()->contratos()->createMany([
            ['data_inicio' => now(), 'status' => 'Ativo'],
            ['data_inicio' => now(), 'status' => 'Cancelado'],
        ]);

        $result = $this->repository->paginate(['status' => 'Ativo']);

        $this->assertEquals(1, $result->total());
    }
}
