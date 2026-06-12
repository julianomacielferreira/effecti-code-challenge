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
use App\Repositories\ServicoRepository;
use App\Models\Servico;
use App\Models\ContratoItem;
use App\Models\Contrato;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DomainException;

class ServicoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ServicoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ServicoRepository();
    }

    /** @test */
    public function cria_servico()
    {
        $this->repository->create([
            'nome' => 'Internet Fibra',
            'valor_base_mensal' => 99.90
        ]);

        $this->assertDatabaseHas('servicos', [
            'nome' => 'Internet Fibra',
            'valor_base_mensal' => 99.90
        ]);
    }

    /** @test */
    public function busca_por_id()
    {
        $servico = Servico::factory()->create(['nome' => 'Backup']);

        $found = $this->repository->find($servico->id);

        $this->assertEquals('Backup', $found->nome);
    }

    /** @test */
    public function paginate_filtra_por_nome()
    {
        Servico::factory()->create(['nome' => 'Cloud']);
        Servico::factory()->create(['nome' => 'Suporte']);

        $page = $this->repository->paginate(['search' => 'Cloud']);

        $this->assertCount(1, $page->items());
        $this->assertEquals('Cloud', $page->items()[0]->nome);
    }

    /** @test */
    public function atualiza_servico()
    {
        $servico = Servico::factory()->create(['valor_base_mensal' => 50]);
        $this->repository->update($servico, ['valor_base_mensal' => 75.50]);

        $this->assertDatabaseHas('servicos', [
            'id' => $servico->id,
            'valor_base_mensal' => 75.50
        ]);
    }

    /** @test */
    public function deleta_servico_sem_vinculo()
    {
        $servico = Servico::factory()->create();

        $result = $this->repository->delete($servico);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('servicos', ['id' => $servico->id]);
    }

    /** @test */
    public function nao_deleta_servico_vinculado_a_contrato()
    {
        $servico = Servico::factory()->create();

        // Cria vínculo mínimo para a regra
        $cliente = Cliente::factory()->create();

        $contrato = Contrato::factory()->create(['cliente_id' => $cliente->id]);

        ContratoItem::factory()->create([
            'contrato_id' => $contrato->id,
            'servico_id' => $servico->id,
            'quantidade' => 1,
            'valor_unitario' => 100
        ]);

        $this->expectException(DomainException::class);

        $this->expectExceptionMessage('vinculado a contratos');

        $this->repository->delete($servico);
    }
}
