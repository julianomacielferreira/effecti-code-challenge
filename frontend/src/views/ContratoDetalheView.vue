<template>
  <div v-if="contrato">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Contrato #{{ contrato.id }}</h2>
      <router-link to="/contratos" class="text-blue-600">← Voltar</router-link>
    </div>

    <div class="bg-white p-4 rounded shadow mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
      <div><strong>Cliente:</strong> {{ contrato.cliente?.nome }}</div>
      <div><strong>Início:</strong> {{ formatDate(contrato.data_inicio) }}</div>
      <div><strong>Status:</strong> {{ contrato.status }}</div>
      <div><strong>Total Mensal:</strong> R$ {{ total.toFixed(2) }}</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-3">Itens do Contrato</h3>

      <div v-if="apiError" class="bg-red-50 border border-red-400 text-red-700 px-3 py-2 rounded mb-3 text-sm">
        {{ apiError }}
      </div>

      <form @submit.prevent="adicionarItem" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">

        <select v-model="item.servico_id" @change="onServicoChange" required class="border p-2 rounded">
          <option value="">Serviço</option>
          <option v-for="servico in servicos" :key="servico.id" :value="servico.id">
            {{ servico.nome }} (R$ {{ servico.valor_base_mensal }})
          </option>
        </select>
        <p v-if="errors.servico" class="text-red-600 text-xs mt-1">{{ errors.servico }}</p>

        <div>
          <input v-model.number="item.quantidade" type="number" min="1" placeholder="Qtd" required
            class="border p-2 rounded" />
          <p v-if="errors.quantidade" class="text-red-600 text-xs mt-1">{{ errors.quantidade }}</p>
        </div>

        <div>
          <input :value="valorItemMask" @input="onValorItemInput" type="text" inputmode="decimal"
            placeholder="Valor unitário" required class="border p-2 rounded" />
          <p v-if="errors.valor" class="text-red-600 text-xs mt-1">{{ errors.valor }}</p>
        </div>

        <button class="bg-cyan-600 text-white rounded px-4">Adicionar</button>
      </form>

      <table class="w-full">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-2">Serviço</th>
            <th>Qtd</th>
            <th>Valor Unit.</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in contrato.itens || []" :key="item.id" class="border-t">
            <td class="p-2">{{ item.servico?.nome || item.servico_id }}</td>
            <td>{{ item.quantidade }}</td>
            <td>{{ formatMoney(item.valor_unitario) }}</td>
            <td>{{ formatMoney(item.quantidade * item.valor_unitario) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 p-3 bg-slate-50 rounded">
        <p class="text-sm text-gray-600">Regra de negócio aplicada: desconto de 10% para quantidade ≥ 10 em um item
          (exemplo implementado no frontend para demonstração).</p>
      </div>
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
import API from '../services/api';

const props = defineProps(['id']);
const contrato = ref(null);
const servicos = ref([]);
const item = ref({ servico_id: '', quantidade: 1, valor_unitario: null });
const apiError = ref('');
const errors = ref({ servico: '', quantidade: '', valor: '' });

const formatMoney = (val) => {

  return Number(val || 0).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  });

};

function formatDate(dateString) {

  if (!dateString) {
    return '-';
  }

  return new Date(dateString).toLocaleDateString('pt-BR');
}

const valorItemMask = computed(() => {

  if (item.value.valor_unitario == null) {
    return '';
  }

  return Number(item.value.valor_unitario).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

function onValorItemInput(element) {

  const digits = element.target.value.replace(/\D/g, '');

  const num = digits ? parseInt(digits, 10) / 100 : null;

  item.value.valor_unitario = num;

  element.target.value = num != null ? valorItemMask.value : '';
}

function onServicoChange() {

  const servico = servicos.value.find(s => s.id == item.value.servico_id);

  if (servico) {
    item.value.valor_unitario = Number(servico.valor_base_mensal);
  }
}

const total = computed(() => {

  if (!contrato.value?.itens) {
    return 0;
  }

  return contrato.value.itens.reduce((sum, item) => {

    let subtotal = item.quantidade * item.valor_unitario;

    //@TODO - Remover Regra de negócio é aplicada no backend: desconto por quantidade
    if (item.quantidade >= 10) subtotal *= 0.9;

    return sum + subtotal;

  }, 0);

});

function handleApiError(error) {

  const msg = error.response?.data?.message || 'Erro ao processar requisição';

  apiError.value = msg;

  const lower = msg.toLowerCase();

  if (lower.includes('serviço') || lower.includes('servico')) {

    errors.value.servico = msg;

  } else if (lower.includes('quantidade')) {

    errors.value.quantidade = msg;

  } else if (lower.includes('valor')) {

    errors.value.valor = msg;

  }
}

async function carregar() {

  try {

    const { data } = await API.getContrato(props.id);

    contrato.value = data;

    const response = await API.getServicos();

    servicos.value = Array.isArray(response.data) ? response.data : response.data.data || [];

  } catch (error) {
    handleApiError(error);
  }
}

async function adicionarItem() {

  apiError.value = '';

  errors.value = { servico: '', quantidade: '', valor: '' };

  if (!item.value.servico_id) {

    errors.value.servico = 'Selecione um serviço';

    return;
  }

  if (!item.value.quantidade || item.value.quantidade < 1) {

    errors.value.quantidade = 'Quantidade inválida';

    return;
  }

  if (!item.value.valor_unitario) {

    const servico = servicos.value.find(servico => servico.id == item.value.servico_id);

    item.value.valor_unitario = servico?.valor_base_mensal;

  }

  try {

    await API.addItem(props.id, item.value);

    item.value = { servico_id: '', quantidade: 1, valor_unitario: null };

    carregar();

  } catch (error) {
    handleApiError(error);
  }
}

onMounted(carregar);
</script>
