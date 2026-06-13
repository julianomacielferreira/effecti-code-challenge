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
import { ICliente } from './Cliente';
import { IServico } from './Servico';

export interface IContratoItem {
    id: number;
    servico_id?: number;
    servico?: IServico;
    quantidade?: number;
    valor_unitario?: number;
}

export interface IContratoTotais {
    subtotal: number;
    desconto_total: number;
    total: number;
    regras_aplicadas?: string[];
}

export interface IContrato {
    id: number;
    cliente_id: number;
    cliente?: ICliente;
    data_inicio: string;
    data_termino?: string;
    status: 'Ativo' | 'Cancelado' | string;
    valor_total: number;
    itens?: IContratoItem[];
    totais?: IContratoTotais;
    updated_at: string;
}

export interface IContratoForm {
    cliente_id: number | string;
    data_inicio: string;
    status: 'Ativo' | 'Cancelado';
}

export interface IContratoErrors {
    cliente: string;
    data: string;
}

export interface IContratoItemForm {
    servico_id: number | string;
    quantidade: number;
    valor_unitario: number | null;
}

export interface IContratoItemErrors {
    servico: string;
    quantidade: string;
    valor: string;
}