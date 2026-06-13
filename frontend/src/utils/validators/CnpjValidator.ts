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

import { StringUtils } from '../StringUtils';
import { IDocumentValidator } from './IDocumentValidator';

export class CnpjValidator implements IDocumentValidator {

    public isValid(cnpj: string): boolean {

        const cnpjSemMascara = StringUtils.onlyDigits(cnpj);

        if (cnpjSemMascara.length !== 14 || /^(\d)\1+$/.test(cnpjSemMascara)) {
            return false;
        }

        const calcularDigito = (pesos: number[]): number => {
            let soma = 0;
            for (let i = 0; i < pesos.length; i++) {
                soma += parseInt(cnpjSemMascara[i]) * pesos[i];
            }
            const resto = soma % 11;
            return resto < 2 ? 0 : 11 - resto;
        };

        const pesosPrimeiro = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        const pesosSegundo = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        const primeiroDigito = calcularDigito(pesosPrimeiro);

        if (primeiroDigito !== parseInt(cnpjSemMascara[12])) {
            return false;
        }

        const segundoDigito = calcularDigito(pesosSegundo);

        return segundoDigito === parseInt(cnpjSemMascara[13]);
    }
}