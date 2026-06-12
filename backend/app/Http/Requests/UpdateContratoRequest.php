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

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContratoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cliente_id'   => 'sometimes|required|integer|exists:clientes,id',
            'data_inicio'  => 'sometimes|required|date',
            'data_termino' => 'nullable|date|after_or_equal:data_inicio',
            'status'       => 'sometimes|nullable|in:Ativo,Cancelado',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // pega o ID da rota, seja {id} ou {contrato}
            $contratoId = $this->route('contrato') ?? $this->route('id');

            if ($contratoId) {
                $contrato = \App\Models\Contrato::find($contratoId);
                if ($contrato && $contrato->status === 'Cancelado') {
                    $validator->errors()->add('contrato', 'Contrato cancelado não pode ser editado.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'cliente_id.required' => 'O cliente é obrigatório.',
            'cliente_id.integer'  => 'ID do cliente inválido.',
            'cliente_id.exists'   => 'Cliente não encontrado.',

            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date'     => 'Data de início deve ser uma data válida.',

            'data_termino.date'           => 'Data de término deve ser uma data válida.',
            'data_termino.after_or_equal' => 'Data de término deve ser maior ou igual à data de início.',

            'status.in' => 'Status deve ser Ativo ou Cancelado.',
        ];
    }
}
