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
use App\Repositories\ClienteRepository;
use App\Models\Cliente;
use App\Models\Contrato;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DomainException;

class ClienteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ClienteRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ClienteRepository();
    }

    /** @test */
    public function cria_cliente_limpando_mascara()
    {
        $cliente = $this->repo->create([
            'nome' => 'MLOCKS CONSULTORIA',
            'cpf_cnpj' => '529.982.247-25',
            'email' => 'contato@email.com',
        ]);

        $this->assertEquals('52998224725', $cliente->cpf_cnpj);
        $this->assertDatabaseHas('clientes', ['email' => 'contato@email.com']);
    }

    /** @test */
    public function busca_por_cpf_ignorando_mascara()
    {
        $this->repo->create([
            'nome' => 'Teste',
            'cpf_cnpj' => '11222333000181',
            'email' => 't@teste.com',
        ]);

        $found = $this->repo->findByCpfCnpj('11.222.333/0001-81');

        $this->assertNotNull($found);
        $this->assertEquals('Teste', $found->nome);
    }

    /** @test */
    public function nao_deleta_cliente_com_contrato_ativo()
    {
        $cliente = $this->repo->create([
            'nome' => 'Com Contrato',
            'cpf_cnpj' => '12345678901',
            'email' => 'c@teste.com',
        ]);

        // cria contrato para travar
        Contrato::create([
            'cliente_id' => $cliente->id,
            'data_inicio' => now(),
            'status' => 'Ativo',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('possui contratos');

        $this->repo->delete($cliente);
    }

    /** @test */
    public function deleta_cliente_sem_contrato()
    {
        $cliente = $this->repo->create([
            'nome' => 'Livre',
            'cpf_cnpj' => '98765432100',
            'email' => 'l@teste.com',
        ]);

        $this->assertTrue($this->repo->delete($cliente));
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }
}
