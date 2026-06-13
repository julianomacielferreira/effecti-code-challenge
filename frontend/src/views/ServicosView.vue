<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Serviços</h2>
      <button @click="abrirNovo" class="bg-slate-900 text-white px-4 py-2 rounded">
        {{ showForm ? 'Cancelar' : 'Novo Serviço' }}
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">

      <div v-if="apiError" class="bg-red-50 border border-red-400 text-red-700 px-3 py-2 rounded mb-3 text-sm">
        {{ apiError }}
      </div>

      <form @submit.prevent="salvar" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <input v-model="form.nome" @blur="validarNome" @input="apiError = ''" placeholder="Nome do serviço" required
            class="border p-2 rounded w-full" :class="{ 'border-red-500': errors.nome }" />
          <p v-if="errors.nome" class="text-red-600 text-xs mt-1">{{ errors.nome }}</p>
        </div>

        <div>
          <input :value="valorMask" @input="onValorInput" placeholder="Valor base mensal" required
            class="border p-2 rounded w-full" :class="{ 'border-red-500': errors.valor }" />
          <p v-if="errors.valor" class="text-red-600 text-xs mt-1">{{ errors.valor }}</p>
        </div>

        <button :disabled="!formValido" class="bg-cyan-600 text-white rounded px-4 disabled:opacity-50">
          {{ editandoId ? 'Atualizar' : 'Salvar' }}
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
          <tr v-if="servicos.length === 0">
            <td colspan="4" class="p-6 text-center text-gray-500">
              Nenhum serviço encontrado.
            </td>
          </tr>

          <tr v-for="servico in servicos" :key="servico.id" class="border-t">
            <td class="p-3">{{ servico.id }}</td>
            <td>{{ servico.nome }}</td>
            <td>{{ CurrencyUtils.formatCurrency(servico.valor_base_mensal) }}</td>
            <td class="space-x-2">
              <button @click="editar(servico)" class="text-blue-600 text-sm">Editar</button>
              <button @click="remover(servico.id)" class="text-red-600 text-sm">Excluir</button>
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
import { CurrencyUtils } from '../utils/CurrencyUtils';
import { ApiHelper } from '../utils/ApiHelper';
import API from '../services/api';

interface IServico {
  id: number;
  nome: string;
  valor_base_mensal: number;
}

interface IServicoForm {
  nome: string;
  valor_base_mensal: number | null;
}

interface IServicoErrors {
  nome: string;
  valor: string;
}

class ServicoViewModel {

  public servicos: Ref<IServico[]> = ref([]);
  public showForm: Ref<boolean> = ref(false);
  public editandoId: Ref<number | null> = ref(null);
  public form: Ref<IServicoForm> = ref({ nome: '', valor_base_mensal: null });
  public busca: Ref<string> = ref('');
  public errors: Ref<IServicoErrors> = ref({ nome: '', valor: '' });
  public apiError: Ref<string> = ref('');

  public get valorMask() {
    return computed(() => CurrencyUtils.formatBRL(this.form.value.valor_base_mensal));
  }

  public get formValido() {
    return computed(() => this.validarNome() && this.validarValor());
  }

  public validarNome = (): boolean => {

    const isInvalid = this.form.value.nome.trim().length < 3;
    this.errors.value.nome = isInvalid ? 'Mínimo 3 caracteres' : '';
    return !isInvalid;
  };

  public validarValor = (): boolean => {

    const value = Number(this.form.value.valor_base_mensal);
    const isInvalid = !value || value <= 0;
    this.errors.value.valor = isInvalid ? 'Valor deve ser maior que zero' : '';
    return !isInvalid;
  };

  public onValorInput = (event: Event): void => {

    const target = event.target as HTMLInputElement;
    const value = CurrencyUtils.parseFromMask(target.value);

    this.form.value.valor_base_mensal = value;
    target.value = value != null ? CurrencyUtils.formatBRL(value) : '';
  };

  private handleApiError = (error: any): void => {

    ApiHelper.mapErrorToFields(
      error,
      this.errors.value as unknown as Record<string, string>,
      this.apiError,
      { 'nome': 'nome', 'valor': 'valor' }
    );
  };

  public abrirNovo = (): void => {

    this.showForm.value = !this.showForm.value;
    if (!this.showForm.value) this.resetForm();
  };

  private resetForm = (): void => {

    this.form.value = { nome: '', valor_base_mensal: null };
    this.errors.value = { nome: '', valor: '' };
    this.apiError.value = '';
    this.editandoId.value = null;
  };

  public editar = (servico: IServico): void => {

    this.editandoId.value = servico.id;
    this.form.value = { nome: servico.nome, valor_base_mensal: servico.valor_base_mensal };
    this.showForm.value = true;
  };

  public carregar = async (): Promise<void> => {

    try {
      const { data } = await API.getServicos({ search: this.busca.value });
      this.servicos.value = Array.isArray(data) ? data : data.data || [];
    } catch (error) {
      this.handleApiError(error);
    }
  };

  public salvar = async (): Promise<void> => {

    this.apiError.value = '';

    if (!this.validarNome() || !this.validarValor()) return;

    try {
      if (this.editandoId.value) {
        await API.updateServico(this.editandoId.value, this.form.value);
      } else {
        await API.createServico(this.form.value);
      }

      this.resetForm();
      this.showForm.value = false;
      await this.carregar();
    } catch (error) {
      this.handleApiError(error);
    }
  };

  public remover = async (id: number): Promise<void> => {

    if (!confirm('Excluir serviço?')) return;

    try {
      await API.deleteServico(id);
      await this.carregar();
    } catch (error) {
      this.handleApiError(error);
    }
  };
}

const servicoViewModel = new ServicoViewModel();

const { servicos, showForm, editandoId, form, busca, errors, apiError, valorMask, formValido } = servicoViewModel;

const { validarNome, onValorInput, abrirNovo, carregar, salvar, editar, remover } = servicoViewModel;

onMounted(carregar);

defineExpose({ CurrencyUtils });
</script>