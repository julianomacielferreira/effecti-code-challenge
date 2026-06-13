<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Contratos</h2>
      <button @click="showForm = !showForm" class="bg-slate-900 text-white px-4 py-2 rounded">
        {{ showForm ? 'Cancelar' : 'Novo Contrato' }}
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">
      <form @submit.prevent="salvar" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <select v-model="form.cliente_id" @change="validarForm" required class="border p-2 rounded w-full"
            :class="{ 'border-red-500': errors.cliente }">
            <option value="">Selecione cliente</option>
            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
              {{ cliente.nome }}
            </option>
          </select>
          <p v-if="errors.cliente" class="text-red-600 text-xs mt-1">{{ errors.cliente }}</p>
        </div>
        <div>
          <input v-model="form.data_inicio" @change="validarForm" type="date" required class="border p-2 rounded w-full"
            :class="{ 'border-red-500': errors.data }" />
          <p v-if="errors.data" class="text-red-600 text-xs mt-1">{{ errors.data }}</p>
        </div>
        <select v-model="form.status" class="border p-2 rounded">
          <option>Ativo</option>
          <option>Cancelado</option>
        </select>
        <button :disabled="!formValido" class="bg-cyan-600 text-white rounded px-4 disabled:opacity-50">Criar</button>
      </form>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3">ID</th>
            <th>Cliente</th>
            <th>Início</th>
            <th>Status</th>
            <th>Valor Total</th>
            <th>Itens</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contrato in contratos" :key="contrato.id" class="border-t hover:bg-gray-50">
            <td class="p-3">{{ contrato.id }}</td>
            <td>{{ contrato.cliente?.nome || contrato.cliente_id }}</td>
            <td>{{ contrato.data_inicio }}</td>
            <td>{{ contrato.status }}</td>
            <td>R$ {{ calcularTotal(contrato).toFixed(2) }}</td>
            <td>
              <span :class="(contrato.itens?.length || 0) === 0 ? 'text-red-600 font-bold' : 'text-green-600'">
                {{ contrato.itens?.length || 0 }}
              </span>
            </td>
            <td class="space-x-2">
              <router-link :to="`/contratos/${contrato.id}`" class="text-blue-600 text-sm">Ver</router-link>
              <button @click="toggleStatus(contrato)" class="text-sm text-amber-600">
                {{ contrato.status === 'Ativo' ? 'Cancelar' : 'Ativar' }}
              </button>
              <button @click="remover(contrato.id)" class="text-sm text-red-600">Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p class="text-xs text-gray-500 p-3">Regra: contrato não pode ser ativado sem pelo menos 1 item.</p>
    </div>
  </div>
</template>

<script setup>
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
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';

const contratos = ref([]);
const clientes = ref([]);
const showForm = ref(false);
const form = ref({ cliente_id: '', data_inicio: new Date().toISOString().slice(0, 10), status: 'Ativo' });
const errors = ref({ cliente: '', data: '' });

function validarForm() {

  errors.value.cliente = !form.value.cliente_id ? 'Selecione um cliente' : '';

  errors.value.data = !form.value.data_inicio ? 'Data de início obrigatória' : '';

  return !errors.value.cliente && !errors.value.data;
}

const formValido = computed(() => validarForm());

function calcularTotal(contrato) {

  if (!contrato.itens) return 0;

  return contrato.itens.reduce((sum, item) => sum + (item.quantidade * item.valor_unitario), 0);
}

async function carregar() {

  const { data } = await api.getContratos();

  contratos.value = Array.isArray(data) ? data : data.data || [];

  const responseClientes = await api.getClientes();

  clientes.value = Array.isArray(responseClientes.data) ? responseClientes.data : responseClientes.data.data || [];
}

async function salvar() {

  if (!validarForm()) return;

  await api.createContrato(form.value);

  showForm.value = false;

  form.value = { cliente_id: '', data_inicio: new Date().toISOString().slice(0, 10), status: 'Ativo' };

  carregar();
}

async function toggleStatus(contrato) {

  const novo = contrato.status === 'Ativo' ? 'Cancelado' : 'Ativo';

  // REGRA DE NEGÓCIO: não ativar sem itens
  if (novo === 'Ativo') {

    const { data } = await api.getContrato(contrato.id);

    if (!data.itens || data.itens.length === 0) {

      alert('Não é possível ativar: contrato precisa ter pelo menos 1 serviço.');

      return;
    }
  }

  await api.updateContrato(contrato.id, { status: novo });

  carregar();
}

async function remover(id) {

  if (confirm('Excluir contrato ?')) {

    await api.deleteContrato(id);

    carregar();
  }
}

onMounted(carregar)
</script>