<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Clientes</h2>
      <button @click="abrirNovo" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800 transition-colors">
        {{ showForm ? 'Cancelar' : 'Novo Cliente' }}
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">
      <h3 class="font-semibold mb-3">{{ editandoId ? 'Editar Cliente' : 'Cadastrar Cliente' }}</h3>

      <div v-if="apiError" class="bg-red-50 border border-red-400 text-red-700 px-3 py-2 rounded mb-3 text-sm">
        {{ apiError }}
      </div>

      <form @submit.prevent="salvar" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <input v-model="form.nome" @input="apiError = ''" placeholder="Nome" required
            class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-cyan-600"
            :class="{ 'border-red-500': errors.nome }" />
          <p v-if="errors.nome" class="text-red-600 text-xs mt-1">{{ errors.nome }}</p>
        </div>

        <div>
          <input v-model="form.cpf_cnpj" @input="onCpfCnpjInput" placeholder="CPF ou CNPJ" required maxlength="18"
            class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-cyan-600"
            :class="{ 'border-red-500': errors.cpf_cnpj }" />
          <p v-if="errors.cpf_cnpj" class="text-red-600 text-xs mt-1">{{ errors.cpf_cnpj }}</p>
        </div>

        <div>
          <input v-model="form.email" @blur="validarEmail" @input="apiError = ''" type="email" placeholder="Email"
            required class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-cyan-600"
            :class="{ 'border-red-500': errors.email }" />
          <p v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email }}</p>
        </div>

        <button :disabled="!formValido"
          class="bg-cyan-600 text-white rounded px-4 hover:bg-cyan-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          {{ editandoId ? 'Atualizar' : 'Salvar' }}
        </button>
      </form>
    </div>

    <div class="flex gap-3 mb-4">
      <input v-model="filtros.search" @input="carregar" placeholder="Buscar por nome..."
        class="border p-2 rounded flex-1 focus:outline-none focus:ring-2 focus:ring-slate-300" />
      <select v-model="filtros.status" @change="carregar"
        class="border p-2 rounded focus:outline-none focus:ring-2 focus:ring-slate-300">
        <option value="">Todos</option>
        <option value="Ativo">Ativo</option>
        <option value="Inativo">Inativo</option>
      </select>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3 font-semibold">ID</th>
            <th class="font-semibold">Nome</th>
            <th class="font-semibold">CPF/CNPJ</th>
            <th class="font-semibold">Email</th>
            <th class="font-semibold">Status</th>
            <th class="font-semibold">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="clientes.length === 0">
            <td colspan="6" class="p-6 text-center text-gray-500">
              Nenhum cliente encontrado.
            </td>
          </tr>

          <tr v-for="cliente in clientes" :key="cliente.id" class="border-t hover:bg-gray-50 transition-colors">
            <td class="p-3">{{ cliente.id }}</td>
            <td>{{ cliente.nome }}</td>
            <td>{{ DocumentFormatter.maskCpfCnpj(cliente.cpf_cnpj) }}</td>
            <td>{{ cliente.email }}</td>
            <td>
              <span class="px-2 py-1 rounded text-xs font-medium"
                :class="cliente.status === 'Ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                {{ cliente.status || 'Ativo' }}
              </span>
            </td>
            <td class="space-x-3">
              <button @click="editar(cliente)" class="text-sm text-blue-600 hover:underline">
                Editar
              </button>
              <button @click="toggleStatus(cliente)" class="text-sm text-amber-600 hover:underline">
                {{ cliente.status === 'Ativo' ? 'Inativar' : 'Ativar' }}
              </button>
              <button @click="remover(cliente.id)" class="text-sm text-red-600 hover:underline">
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
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
import { ref, onMounted, computed, Ref } from 'vue';
import { StringUtils } from '../utils/StringUtils';
import { DocumentFormatter } from '../utils/DocumentFormatter';
import { EmailValidator } from '../utils/EmailValidator';
import { DocumentValidatorFactory } from '../utils/validators/DocumentValidatorFactory';
import API from '../services/api';

interface ICliente {
  id: number;
  nome: string;
  cpf_cnpj: string;
  email: string;
  status: 'Ativo' | 'Inativo';
}

interface IClienteForm {
  nome: string;
  cpf_cnpj: string;
  email: string;
}

interface IFiltros {
  search: string;
  status: string;
}

interface IFormErros {
  nome: string;
  cpf_cnpj: string;
  email: string;
}

class ClienteViewModel {

  public clientes: Ref<ICliente[]> = ref([]);
  public showForm: Ref<boolean> = ref(false);
  public editandoId: Ref<number | null> = ref(null);
  public form: Ref<IClienteForm> = ref({ nome: '', cpf_cnpj: '', email: '' });
  public filtros: Ref<IFiltros> = ref({ search: '', status: '' });
  public errors: Ref<IFormErros> = ref({ nome: '', cpf_cnpj: '', email: '' });
  public apiError: Ref<string> = ref('');

  public get formValido() {

    return computed(() => {
      return (
        this.form.value.nome.length > 2 &&
        !this.errors.value.cpf_cnpj &&
        !this.errors.value.email &&
        StringUtils.onlyDigits(this.form.value.cpf_cnpj).length >= 11
      );
    });
  }

  public onCpfCnpjInput = (event: Event): void => {

    const target = event.target as HTMLInputElement;

    this.form.value.cpf_cnpj = DocumentFormatter.maskCpfCnpj(target.value);

    this.validarCpfCnpj();
  };

  public validarCpfCnpj = (): boolean => {

    const value = StringUtils.onlyDigits(this.form.value.cpf_cnpj);

    if (!value) {
      this.errors.value.cpf_cnpj = 'Obrigatório';
      return false;
    }

    const validator = DocumentValidatorFactory.getValidator(value);

    if (!validator) {
      this.errors.value.cpf_cnpj = 'Digite 11 dígitos (CPF) ou 14 (CNPJ)';
      return false;
    }

    const isValid = validator.isValid(value);

    this.errors.value.cpf_cnpj = isValid
      ? ''
      : (value.length === 11 ? 'CPF inválido' : 'CNPJ inválido');

    return isValid;
  };

  public validarEmail = (): boolean => {

    const isValid = EmailValidator.isValid(this.form.value.email);

    this.errors.value.email = isValid ? '' : 'Email inválido';

    return isValid;
  };

  private handleApiError = (error: any): void => {

    const msg = error.response?.data?.message || 'Erro ao processar requisição';

    this.apiError.value = msg;

    const lower = msg.toLowerCase();

    if (lower.includes('cpf') || lower.includes('cnpj')) {
      this.errors.value.cpf_cnpj = msg;
    } else if (lower.includes('email')) {
      this.errors.value.email = msg;
    } else if (lower.includes('nome')) {
      this.errors.value.nome = msg;
    }
  };

  public abrirNovo = (): void => {

    this.editandoId.value = null;

    this.resetForm();

    this.showForm.value = !this.showForm.value;
  };

  public editar = (cliente: ICliente): void => {

    this.editandoId.value = cliente.id;

    this.form.value = {
      nome: cliente.nome,
      cpf_cnpj: DocumentFormatter.maskCpfCnpj(cliente.cpf_cnpj),
      email: cliente.email
    };

    this.resetErrors();

    this.showForm.value = true;

    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  private resetForm = (): void => {
    this.form.value = { nome: '', cpf_cnpj: '', email: '' };
    this.resetErrors();
  };

  private resetErrors = (): void => {
    this.errors.value = { nome: '', cpf_cnpj: '', email: '' };
    this.apiError.value = '';
  };

  public carregar = async (): Promise<void> => {
    try {
      const { data } = await API.getClientes(this.filtros.value);
      this.clientes.value = Array.isArray(data) ? data : data.data || [];
    } catch (e) {
      this.handleApiError(e);
    }
  };

  public salvar = async (): Promise<void> => {

    this.apiError.value = '';

    this.errors.value.nome = this.form.value.nome.length < 3 ? 'Mínimo 3 caracteres' : '';

    if (!this.validarCpfCnpj() || !this.validarEmail() || this.errors.value.nome) {
      return;
    }

    try {
      if (this.editandoId.value) {
        await API.updateCliente(this.editandoId.value, this.form.value);
      } else {
        await API.createCliente(this.form.value);
      }

      this.showForm.value = false;
      this.editandoId.value = null;
      this.resetForm();

      await this.carregar();

    } catch (e) {
      this.handleApiError(e);
    }
  };

  public toggleStatus = async (cliente: ICliente): Promise<void> => {
    try {

      const novoStatus = cliente.status === 'Ativo' ? 'Inativo' : 'Ativo';

      await API.updateCliente(cliente.id, { status: novoStatus });

      await this.carregar();

    } catch (e) {
      this.handleApiError(e);
    }
  };

  public remover = async (id: number): Promise<void> => {

    if (!confirm('Excluir cliente?')) return;

    try {
      await API.deleteCliente(id);
      await this.carregar();
    } catch (e) {
      this.handleApiError(e);
    }
  };
}

const clienteViewModel = new ClienteViewModel();

const { clientes, showForm, editandoId, form, errors, apiError, filtros, formValido } = clienteViewModel;

const { onCpfCnpjInput, validarEmail, abrirNovo, editar, carregar, salvar, toggleStatus, remover } = clienteViewModel;

onMounted(carregar);

defineExpose({ DocumentFormatter });
</script>