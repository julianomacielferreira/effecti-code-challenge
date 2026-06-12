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

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Services\ClienteService;
use Illuminate\Http\Request;
use DomainException;

/**
 * @OA\Info(title="Effecti ERP API", version="1.0")
 * @OA\Tag(name="Clientes", description="CRUD de clientes - Regras 1,2,3 do PDF")
 */
class ClienteController extends Controller
{
    private $service;

    public function __construct(ClienteService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *   path="/api/clientes",
     *   tags={"Clientes"},
     *   summary="Lista paginada",
     *   @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"Ativo","Inativo"})),
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Cliente"))))
     * )
     */
    public function index(Request $request)
    {
        return response()->json($this->service->listar($request->all()));
    }

    /**
     * @OA\Post(
     *   path="/api/clientes",
     *   tags={"Clientes"},
     *   summary="Cria cliente - valida CPF/CNPJ",
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ClienteStore")),
     *   @OA\Response(response=201, description="Criado", @OA\JsonContent(ref="#/components/schemas/Cliente")),
     *   @OA\Response(response=422, description="CPF duplicado ou inválido")
     * )
     */
    public function store(StoreClienteRequest $request)
    {
        $c = $this->service->criar($request->validated());
        return response()->json($c, 201);
    }

    /**
     * @OA\Get(path="/api/clientes/{id}", tags={"Clientes"}, summary="Detalha",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Cliente"))
     * )
     */
    public function show($id)
    {
        return response()->json($this->service->buscar($id));
    }

    /**
     * @OA\Put(path="/api/clientes/{id}", tags={"Clientes"}, summary="Atualiza",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ClienteUpdate")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function update(UpdateClienteRequest $request, $id)
    {
        return response()->json($this->service->atualizar($id, $request->validated()));
    }

    /**
     * @OA\Delete(path="/api/clientes/{id}", tags={"Clientes"}, summary="Exclui - bloqueia se tiver contrato",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Sem conteúdo"),
     *   @OA\Response(response=422, description="Possui contratos", @OA\JsonContent(@OA\Property(property="message", type="string")))
     * )
     */
    public function destroy($id)
    {
        try {
            $this->service->excluir($id);
            return response()->json(null, 204);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
