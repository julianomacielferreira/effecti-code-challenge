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

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function render($request, Throwable $ex)
    {
        if ($request->expectsJson() || $request->is('api/*')) {

            // 404 - Model não encontrado
            if ($ex instanceof ModelNotFoundException) {
                return $this->modelResponse($ex);
            }

            // Laravel 8 converte ModelNotFound em NotFoundHttpException
            if ($ex instanceof NotFoundHttpException) {
                $prev = $ex->getPrevious();
                if ($prev instanceof ModelNotFoundException) {
                    return $this->modelResponse($prev);
                }
                return response()->json(['message' => 'Recurso não encontrado'], Response::HTTP_NOT_FOUND);
            }

            // 422 - Validação (duplicado, etc)
            if ($ex instanceof ValidationException) {
                return response()->json([
                    'message' => $ex->validator->errors()->first()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return parent::render($request, $ex);
    }

    private function modelResponse(ModelNotFoundException $ex)
    {
        $messages = [
            'Servico'  => 'Serviço não encontrado',
            'Cliente'  => 'Cliente não encontrado',
            'Contrato' => 'Contrato não encontrado',
        ];

        $model = class_basename($ex->getModel());

        return response()->json([
            'message' => $messages[$model] ?? 'Registro não encontrado'
        ], Response::HTTP_NOT_FOUND);
    }
}
