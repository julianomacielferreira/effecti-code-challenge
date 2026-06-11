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

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfCnpj implements Rule
{
    /**
     * Aceita CPF (11 dígitos) OU CNPJ (14 dígitos)
     * com ou sem máscara, e valida o dígito verificador (DV)
     */
    public function passes($attribute, $value)
    {
        // remove tudo que não é número
        $numero = preg_replace('/\D/', '', $value);

        if (strlen($numero) === 11) {
            return $this->validarCpf($numero);
        }

        if (strlen($numero) === 14) {
            return $this->validarCnpj($numero);
        }

        return false;
    }

    public function message()
    {
        return 'O campo :attribute não é um CPF ou CNPJ válido.';
    }

    private function validarCpf(string $cpf): bool
    {
        // Rejeita sequências 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula os 2 dígitos verificadores
        for ($t = 9; $t < 11; $t++) {

            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    private function validarCnpj(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        // Primeiro DV
        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $b[$i + 1];
        }

        $d1 = ($soma % 11) < 2 ? 0 : 11 - ($soma % 11);

        // Segundo DV
        $soma = 0;
        for ($i = 0; $i <= 12; $i++) {
            $soma += $cnpj[$i] * $b[$i];
        }

        $d2 = ($soma % 11) < 2 ? 0 : 11 - ($soma % 11);

        return $cnpj[12] == $d1 && $cnpj[13] == $d2;
    }
}
