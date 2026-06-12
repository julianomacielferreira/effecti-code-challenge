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

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ContratoService;
use App\Http\Requests\StoreContratoRequest;
use App\Http\Requests\UpdateContratoRequest;
use App\Http\Requests\StoreContratoItemRequest;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Contratos",
 *     description="Operações de contratos e itens"
 * )
 */
class ContratoController extends Controller
{
    private ContratoService $service;

    public function __construct(ContratoService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/contratos",
     *     summary="Listar contratos paginados",
     *     tags={"Contratos"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status",
     *         @OA\Schema(type="string", enum={"Ativo", "Cancelado"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->service->listar($request->all());
    }

    /**
     * @OA\Get(
     *     path="/api/contratos/{id}",
     *     summary="Visualizar contrato com itens e total calculado",
     *     tags={"Contratos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contrato encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Contrato")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contrato não encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        return $this->service->buscar((int) $id);
    }

    /**
     * @OA\Post(
     *     path="/api/contratos",
     *     summary="Criar novo contrato",
     *     tags={"Contratos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cliente_id", "data_inicio"},
     *             @OA\Property(property="cliente_id", type="integer", example=1),
     *             @OA\Property(property="data_inicio", type="string", format="date", example="2024-01-01"),
     *             @OA\Property(property="data_termino", type="string", format="date", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"Ativo","Cancelado"}, example="Ativo")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Criado")
     * )
     */
    public function store(StoreContratoRequest $request)
    {
        return response()->json($this->service->criar($request->validated()), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/contratos/{id}",
     *     summary="Atualizar contrato",
     *     tags={"Contratos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Contrato")),
     *     @OA\Response(response=200, description="Atualizado")
     * )
     */
    public function update(UpdateContratoRequest $request, int $id)
    {
        return $this->service->atualizar((int) $id, $request->validated());
    }

    /**
     * @OA\Delete(
     *     path="/api/contratos/{id}",
     *     summary="Excluir contrato",
     *     tags={"Contratos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Excluído")
     * )
     */
    public function destroy(int $id)
    {
        $this->service->excluir((int) $id);
        return response()->noContent();
    }

    /**
     * @OA\Post(
     *     path="/api/contratos/{id}/itens",
     *     summary="Adicionar item ao contrato",
     *     tags={"Contratos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"servico_id","quantidade","valor_unitario"},
     *             @OA\Property(property="servico_id", type="integer", example=5),
     *             @OA\Property(property="quantidade", type="integer", example=10),
     *             @OA\Property(property="valor_unitario", type="number", example=150.00)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Item adicionado")
     * )
     */
    public function addItem(StoreContratoItemRequest $request, int $id)
    {
        return response()->json(
            $this->service->adicionarItem((int) $id, $request->validated()),
            201
        );
    }
}
