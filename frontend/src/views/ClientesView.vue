<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Clientes</h2>
      <button @click="showForm = !showForm" class="bg-slate-900 text-white px-4 py-2 rounded">
        {{ showForm ? 'Cancelar' : 'Novo Cliente' }}
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">
      <h3 class="font-semibold mb-3">Cadastrar Cliente</h3>
      <form @submit.prevent="salvar" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <!-- NOME -->
        <div>
          <input v-model="form.nome" placeholder="Nome" required class="border p-2 rounded w-full"
            :class="{ 'border-red-500': errors.nome }" />
          <p v-if="errors.nome" class="text-red-600 text-xs mt-1">{{ errors.nome }}</p>
        </div>

        <!-- CPF/CNPJ COM MÁSCARA -->
        <div>
          <input v-model="form.cpf_cnpj" @input="onCpfCnpjInput" placeholder="CPF ou CNPJ" required maxlength="18"
            class="border p-2 rounded w-full" :class="{ 'border-red-500': errors.cpf_cnpj }" />
          <p v-if="errors.cpf_cnpj" class="text-red-600 text-xs mt-1">{{ errors.cpf_cnpj }}</p>
        </div>

        <!-- EMAIL -->
        <div>
          <input v-model="form.email" @blur="validarEmail" type="email" placeholder="Email" required
            class="border p-2 rounded w-full" :class="{ 'border-red-500': errors.email }" />
          <p v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email }}</p>
        </div>

        <button :disabled="!formValido" class="bg-cyan-600 text-white rounded px-4 disabled:opacity-50">
          Salvar
        </button>
      </form>
    </div>

    <!-- resto igual -->
    <div class="flex gap-3 mb-4">
      <input v-model="filtros.search" @input="carregar" placeholder="Buscar por nome..."
        class="border p-2 rounded flex-1" />
      <select v-model="filtros.status" @change="carregar" class="border p-2 rounded">
        <option value="">Todos</option>
        <option value="Ativo">Ativo</option>
        <option value="Inativo">Inativo</option>
      </select>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3">ID</th>
            <th>Nome</th>
            <th>CPF/CNPJ</th>
            <th>Email</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in clientes" :key="c.id" class="border-t">
            <td class="p-3">{{ c.id }}</td>
            <td>{{ c.nome }}</td>
            <td>{{ c.cpf_cnpj }}</td>
            <td>{{ c.email }}</td>
            <td><span :class="c.status === 'Ativo' ? 'text-green-600' : 'text-red-600'">{{ c.status || 'Ativo' }}</span>
            </td>
            <td class="space-x-2">
              <button @click="toggleStatus(c)" class="text-sm text-blue-600">{{ c.status === 'Ativo' ? 'Inativar' :
                'Ativar' }}</button>
              <button @click="remover(c.id)" class="text-sm text-red-600">Excluir</button>
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

const clientes = ref([]);
const showForm = ref(false);
const form = ref({ nome: '', cpf_cnpj: '', email: '' });
const filtros = ref({ search: '', status: '' });
const errors = ref({ nome: '', cpf_cnpj: '', email: '' });

const onlyDigits = (value) => value.replace(/\D/g, '');

function maskCpfCnpj(value) {

  const digitos = onlyDigits(value);

  if (digitos.length <= 11) {

    return digitos.replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2');

  }

  return digitos.replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/\.(\d{3})(\d)/, '.$1/$2')
    .replace(/(\d{4})(\d)/, '$1-$2');
}

function onCpfCnpjInput(element) {

  form.value.cpf_cnpj = maskCpfCnpj(element.target.value);

  validarCpfCnpj();
}

function validarCPF(CPF) {

  const cpfSemMascara = onlyDigits(CPF);

  const possuiTamanhoValido = cpfSemMascara.length === 11;

  const todosDigitosIguais = /^(\d)\1+$/.test(cpfSemMascara);

  if (!possuiTamanhoValido || todosDigitosIguais) {
    return false;
  }

  let somaPrimeiroDigitoVerificador = 0;

  for (let indiceDigito = 0; indiceDigito < 9; indiceDigito++) {

    const digitoAtual = parseInt(cpfSemMascara[indiceDigito]);

    const pesoAtual = 10 - indiceDigito;

    somaPrimeiroDigitoVerificador += digitoAtual * pesoAtual;
  }

  let primeiroDigitoCalculado = 11 - (somaPrimeiroDigitoVerificador % 11);

  if (primeiroDigitoCalculado > 9) {
    primeiroDigitoCalculado = 0;
  }

  const primeiroDigitoInformado = parseInt(cpfSemMascara[9]);

  if (primeiroDigitoCalculado !== primeiroDigitoInformado) {
    return false;
  }

  let somaSegundoDigitoVerificador = 0;

  for (let indiceDigito = 0; indiceDigito < 10; indiceDigito++) {

    const digitoAtual = parseInt(cpfSemMascara[indiceDigito]);

    const pesoAtual = 11 - indiceDigito;

    somaSegundoDigitoVerificador += digitoAtual * pesoAtual;
  }

  let segundoDigitoCalculado = 11 - (somaSegundoDigitoVerificador % 11);

  if (segundoDigitoCalculado > 9) {
    segundoDigitoCalculado = 0;
  }

  const segundoDigitoInformado = parseInt(cpfSemMascara[10]);

  return (segundoDigitoCalculado === segundoDigitoInformado);
}

function validarCNPJ(CNPJ) {

  const cnpjSemMascara = onlyDigits(CNPJ);

  const possuiTamanhoValido = cnpjSemMascara.length === 14;

  const todosDigitosIguais = /^(\d)\1+$/.test(cnpjSemMascara);

  if (!possuiTamanhoValido || todosDigitosIguais) {
    return false;
  }

  function calcularDigitoVerificador(quantidadeDigitosBase) {

    const pesosPrimeiroDigito = [
      5, 4, 3, 2,
      9, 8, 7, 6,
      5, 4, 3, 2
    ];

    const pesosSegundoDigito = [
      6, 5, 4, 3,
      2, 9, 8, 7,
      6, 5, 4, 3, 2
    ];

    const pesosUtilizados = quantidadeDigitosBase === 12 ? pesosPrimeiroDigito : pesosSegundoDigito;

    let somaPonderada = 0;

    for (let indiceDigito = 0; indiceDigito < pesosUtilizados.length; indiceDigito++) {

      const digitoAtual = parseInt(cnpjSemMascara[indiceDigito]);

      const pesoAtual = pesosUtilizados[indiceDigito];

      somaPonderada += digitoAtual * pesoAtual;
    }

    const restoDaDivisao = somaPonderada % 11;

    if (restoDaDivisao < 2) {
      return 0;
    }

    return 11 - restoDaDivisao;
  }

  const primeiroDigitoCalculado = calcularDigitoVerificador(12);

  const primeiroDigitoInformado = parseInt(cnpjSemMascara[12]);

  const segundoDigitoCalculado = calcularDigitoVerificador(13);

  const segundoDigitoInformado = parseInt(cnpjSemMascara[13]);

  return (primeiroDigitoCalculado === primeiroDigitoInformado && segundoDigitoCalculado === segundoDigitoInformado);
}

function validarCpfCnpj() {

  const value = onlyDigits(form.value.cpf_cnpj);

  if (!value) {

    errors.value.cpf_cnpj = 'Obrigatório';

    return false;
  }

  if (value.length === 11) {

    const isValid = validarCPF(value);

    errors.value.cpf_cnpj = isValid ? '' : 'CPF inválido';

    return isValid;
  }

  if (value.length === 14) {

    const isValid = validarCNPJ(v);

    errors.value.cpf_cnpj = isValid ? '' : 'CNPJ inválido';

    return isValid;
  }

  errors.value.cpf_cnpj = 'Digite 11 dígitos (CPF) ou 14 (CNPJ)';

  return false;
}

function validarEmail() {

  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  const isValid = regex.test(form.value.email);

  errors.value.email = isValid ? '' : 'Email inválido';

  return isValid;
}

const formValido = computed(() => {

  return form.value.nome.length > 2 &&
    !errors.value.cpf_cnpj &&
    !errors.value.email &&
    onlyDigits(form.value.cpf_cnpj).length >= 11;
})

async function carregar() {

  const { data } = await api.getClientes(filtros.value);

  clientes.value = Array.isArray(data) ? data : data.data || [];
}

async function salvar() {

  errors.value.nome = form.value.nome.length < 3 ? 'Mínimo 3 caracteres' : '';

  const okCpf = validarCpfCnpj();

  const okEmail = validarEmail();

  if (!okCpf || !okEmail || errors.value.nome) return;

  await api.createCliente(form.value);

  form.value = { nome: '', cpf_cnpj: '', email: '' };

  showForm.value = false;

  carregar();
}

async function toggleStatus(c) {

  const novo = c.status === 'Ativo' ? 'Inativo' : 'Ativo';

  await api.updateCliente(c.id, { status: novo });

  carregar();

}

async function remover(id) {

  if (confirm('Excluir cliente?')) {

    await api.deleteCliente(id);

    carregar();
  }

}

onMounted(carregar)
</script>