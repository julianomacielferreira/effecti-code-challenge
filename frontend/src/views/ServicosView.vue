<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Serviços</h2>
      <button @click="showForm = !showForm" class="bg-slate-900 text-white px-4 py-2 rounded">
        {{ showForm ? 'Cancelar' : 'Novo Serviço' }}
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">
      <form @submit.prevent="salvar" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <input v-model="form.nome" @blur="validarNome" placeholder="Nome do serviço" required
            class="border p-2 rounded w-full" :class="{ 'border-red-500': errors.nome }" />
          <p v-if="errors.nome" class="text-red-600 text-xs mt-1">{{ errors.nome }}</p>
        </div>
        <div>
          <input :value="valorMask" @input="onValorInput" placeholder="Valor base mensal" required
            class="border p-2 rounded" />
          <p v-if="errors.valor" class="text-red-600 text-xs mt-1">{{ errors.valor }}</p>
        </div>
        <button :disabled="!formValido" class="bg-cyan-600 text-white rounded px-4 disabled:opacity-50">
          {{ editando ? 'Atualizar' : 'Salvar' }}
        </button>
      </form>
    </div>

    <input v-model="busca" @input="carregar" placeholder="Buscar..." class="border p-2 rounded w-full mb-4" />

    <div class="bg-white rounded shadow overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3">ID</th>
            <th>Nome</th>
            <th>Valor Base</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in servicos" :key="s.id" class="border-t">
            <td class="p-3">{{ s.id }}</td>
            <td>{{ s.nome }}</td>
            <td>R$ {{ Number(s.valor_base_mensal).toFixed(2) }}</td>
            <td class="space-x-2">
              <button @click="editar(s)" class="text-blue-600 text-sm">Editar</button>
              <button @click="remover(s.id)" class="text-red-600 text-sm">Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>
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

const servicos = ref([]);
const showForm = ref(false);
const editando = ref(null);
const form = ref({ nome: '', valor_base_mensal: null });
const busca = ref('');
const errors = ref({ nome: '', valor: '' });

function validarNome() {

  errors.value.nome = form.value.nome.trim().length < 3 ? 'Mínimo 3 caracteres' : '';

  return !errors.value.nome;
}

function validarValor() {

  const value = Number(form.value.valor_base_mensal);

  errors.value.valor = (!value || value <= 0) ? 'Valor deve ser maior que zero' : '';

  return !errors.value.valor;
}

const valorMask = computed(() => {

  if (form.value.valor_base_mensal == null) return '';

  return Number(form.value.valor_base_mensal)
    .toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
})

function onValorInput(element) {

  const digits = element.target.value.replace(/\D/g, '');

  const value = digits ? parseInt(digits, 10) / 100 : null;

  form.value.valor_base_mensal = value;

  element.target.value = value != null ? valorMask.value : '';

}

const formValido = computed(() => validarNome() && validarValor());

async function carregar() {

  const { data } = await api.getServicos({ search: busca.value });

  servicos.value = Array.isArray(data) ? data : data.data || [];

}

async function salvar() {

  if (!validarNome() || !validarValor()) {
    return;
  }

  if (editando.value) {

    await api.updateServico(editando.value, form.value);

  } else {

    await api.createServico(form.value);

  }

  form.value = { nome: '', valor_base_mensal: null };

  showForm.value = false;

  editando.value = null;

  errors.value = { nome: '', valor: '' };

  carregar();
}

function editar(servico) {

  editando.value = servico.id;

  form.value = { nome: servico.nome, valor_base_mensal: servico.valor_base_mensal };

  showForm.value = true;
}

async function remover(id) {

  if (confirm('Excluir serviço?')) {

    await api.deleteServico(id);

    carregar();
  }
}

onMounted(carregar)
</script>