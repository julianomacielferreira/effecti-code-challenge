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

class StoreContratoItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Pega o id do contrato da rota /contratos/{id}/itens
        $contratoId = $this->route('id');

        return [
            'servico_id' => 'required|exists:servicos,id|unique:contrato_itens,servico_id,NULL,id,contrato_id,' . $contratoId,
            'quantidade' => 'required|integer|min:1',
            'valor_unitario' => 'required|numeric|min:0.01',
        ];
    }

    public function messages()
    {
        return [
            'servico_id.required' => 'O serviço é obrigatório.',
            'servico_id.exists' => 'Serviço não encontrado.',
            'servico_id.unique' => 'Este serviço já foi adicionado ao contrato.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.integer' => 'Quantidade deve ser um número inteiro.',
            'quantidade.min' => 'Quantidade deve ser maior que zero.',
            'valor_unitario.required' => 'O valor unitário é obrigatório.',
            'valor_unitario.numeric' => 'Valor unitário inválido.',
            'valor_unitario.min' => 'Valor unitário deve ser maior que zero.',
        ];
    }
}
