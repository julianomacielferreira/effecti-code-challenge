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

namespace App\Services;

use App\Models\Contrato;
use App\Rules\Contrato\ContratoRule;

class CalculadoraDeContrato
{
    /**
     * @var ContratoRule[]
     */
    private array $regras;

    public function __construct(array $regras = [])
    {
        $this->regras = $regras;
    }

    public function calcular(Contrato $contrato): array
    {
        $subtotal = $contrato->itens->sum(function ($item) {
            return $item->quantidade * $item->valor_unitario;
        });

        $valor = $subtotal;
        $aplicadas = [];

        foreach ($this->regras as $regra) {

            $novoValor = $regra->aplicar($contrato, $valor);

            if ($novoValor != $valor) {
                $aplicadas[] = $regra->nome();
            }

            $valor = $novoValor;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($valor, 2),
            'desconto_total' => round($subtotal - $valor, 2),
            'regras_aplicadas' => $aplicadas,
        ];
    }
}
