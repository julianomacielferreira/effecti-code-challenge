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
import axios from 'axios';

const http = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
});

const API = {
    getClientes(params = {}) { return http.get('/clientes', { params }); },
    createCliente(data) { return http.post('/clientes', data); },
    updateCliente(id, data) { return http.put(`/clientes/${id}`, data); },
    deleteCliente(id) { return http.delete(`/clientes/${id}`); },
    getServicos(params = {}) { return http.get('/servicos', { params }); },
    createServico(data) { return http.post('/servicos', data); },
    updateServico(id, data) { return http.put(`/servicos/${id}`, data); },
    deleteServico(id) { return http.delete(`/servicos/${id}`); },
    getContratos() { return http.get('/contratos'); },
    getContrato(id) { return http.get(`/contratos/${id}`); },
    createContrato(data) { return http.post('/contratos', data); },
    updateContrato(id, data) { return http.put(`/contratos/${id}`, data); },
    deleteContrato(id) { return http.delete(`/contratos/${id}`); },
    addItem(id, data) { return http.post(`/contratos/${id}/itens`, data); }
}

export default API