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
use App\Rules\CpfCnpj;

class StoreClienteRequest extends FormRequest
{
    public function authorize()
    {
        // Em produção usar Auth::user()->can()
        return true;
    }

    public function rules()
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => ['required', 'string', new CpfCnpj(), 'unique:clientes,cpf_cnpj'],
            'email' => ['required', 'email', 'max:255', 'unique:clientes,email'],
            'status' => ['sometimes', 'in:Ativo,Inativo'],
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'Nome é obrigatório',
            'cpf_cnpj.required' => 'CPF/CNPJ é obrigatório',
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado.',
            'email.email'       => 'Email inválido',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('cpf_cnpj')) {
            $this->merge([
                'cpf_cnpj' => preg_replace('/\D/', '', $this->cpf_cnpj),
            ]);
        }
    }
}
