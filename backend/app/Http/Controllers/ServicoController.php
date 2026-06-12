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

use App\Http\Requests\StoreServicoRequest;
use App\Http\Requests\UpdateServicoRequest;
use App\Services\ServicoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DomainException;

/**
 * @OA\Tag(
 *     name="Serviços",
 *     description="CRUD de serviços - Regra: não excluir se estiver em contrato"
 * )
 */
class ServicoController extends Controller
{
    private ServicoService $service;

    public function __construct(ServicoService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *   path="/api/servicos",
     *   tags={"Serviços"},
     *   summary="Lista paginada de serviços",
     *   description="Retorna serviços com filtro opcional por nome",
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     description="Filtra por nome",
     *     required=false,
     *     @OA\Schema(type="string", example="Internet")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="current_page", type="integer", example=1),
     *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Servico")),
     *       @OA\Property(property="total", type="integer", example=2)
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        return response()->json($this->service->listar($request->all()));
    }

    /**
     * @OA\Post(
     *   path="/api/servicos",
     *   tags={"Serviços"},
     *   summary="Cria um novo serviço",
     *   @OA\RequestBody(
     *     required=true,
     *     description="Dados do serviço",
     *     @OA\JsonContent(ref="#/components/schemas/ServicoStore")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Criado",
     *     @OA\JsonContent(ref="#/components/schemas/Servico")
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validação falhou (nome duplicado ou valor inválido)"
     *   )
     * )
     */
    public function store(StoreServicoRequest $request)
    {
        $servico = $this->service->criar($request->validated());

        return response()->json($servico, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *   path="/api/servicos/{id}",
     *   tags={"Serviços"},
     *   summary="Detalha um serviço",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID do serviço",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/Servico")
     *   ),
     *   @OA\Response(response=404, description="Não encontrado")
     * )
     */
    public function show(int $id)
    {
        return response()->json($this->service->buscar($id));
    }

    /**
     * @OA\Put(
     *   path="/api/servicos/{id}",
     *   tags={"Serviços"},
     *   summary="Atualiza serviço",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/ServicoUpdate")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Atualizado",
     *     @OA\JsonContent(ref="#/components/schemas/Servico")
     *   ),
     *   @OA\Response(response=422, description="Validação")
     * )
     */
    public function update(UpdateServicoRequest $request, int $id)
    {
        $this->service->buscar((int)$id); // força 404

        $servico = $this->service->atualizar($id, $request->validated());

        return response()->json($servico);
    }

    /**
     * @OA\Delete(
     *   path="/api/servicos/{id}",
     *   tags={"Serviços"},
     *   summary="Exclui serviço - bloqueia se estiver em uso",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(response=204, description="Excluído com sucesso"),
     *   @OA\Response(
     *     response=422,
     *     description="Serviço em uso",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Serviço está vinculado a contratos e não pode ser excluído.")
     *     )
     *   )
     * )
     */
    public function destroy(int $id)
    {
        try {

            $this->service->excluir($id);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (DomainException $ex) {

            return response()->json(
                ['message' => $ex->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
