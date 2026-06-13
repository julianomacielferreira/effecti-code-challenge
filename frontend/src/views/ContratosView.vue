<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Contratos</h2>
      <button @click="showForm = !showForm" class="bg-slate-900 text-white px-4 py-2 rounded">
        {{ showForm ? 'Cancelar' : 'Novo Contrato' }}
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">

      <div v-if="apiError" class="bg-red-50 border border-red-400 text-red-700 px-3 py-2 rounded mb-3 text-sm">
        {{ apiError }}
      </div>

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
          <option value="Ativo">Ativo</option>
          <option value="Cancelado">Cancelado</option>
        </select>

        <button :disabled="!formValido" class="bg-cyan-600 text-white rounded px-4 disabled:opacity-50">
          Criar
        </button>
      </form>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3">ID</th>
            <th>Cliente</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Status</th>
            <th>Valor Total Mensal</th>
            <th>Itens</th>
            <th>Atualização</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="contratos.length === 0">
            <td colspan="9" class="p-6 text-center text-gray-500">
              Nenhum contrato encontrado.
            </td>
          </tr>

          <tr v-for="contrato in contratos" :key="contrato.id" class="border-t hover:bg-gray-50">
            <td class="p-3">{{ contrato.id }}</td>
            <td>{{ contrato.cliente?.nome || contrato.cliente_id }}</td>
            <td>{{ DateUtils.formatPTBR(contrato.data_inicio) }}</td>
            <td>{{ DateUtils.formatPTBR(contrato.data_termino) }}</td>
            <td>
              <span :class="contrato.status === 'Ativo' ? 'text-green-600' : 'text-red-600'">
                {{ contrato.status }}
              </span>
            </td>
            <td>{{ CurrencyUtils.formatCurrency(contrato.valor_total) }}</td>
            <td>
              <span :class="(contrato.itens?.length || 0) === 0 ? 'text-red-600 font-bold' : 'text-green-600'">
                {{ contrato.itens?.length || 0 }}
              </span>
            </td>
            <td>{{ DateUtils.formatPTBR(contrato.updated_at) }}</td>
            <td class="space-x-2">
              <router-link :to="`/contratos/${contrato.id}`" class="text-blue-600 text-sm hover:underline">
                Adicionar Itens
              </router-link>
              <button v-if="contrato.status === 'Ativo'" @click="toggleStatus(contrato)"
                class="text-sm text-amber-600 hover:underline">
                Cancelar
              </button>
              <button @click="remover(contrato.id)" class="text-sm text-red-600 hover:underline">
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p class="text-xs text-gray-500 p-3">
        Regra: Contratos com status "Cancelado" não podem ser editados.
      </p>
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
import { DateUtils } from '../utils/DateUtils';
import { CurrencyUtils } from '../utils/CurrencyUtils';
import { ApiHelper } from '../utils/ApiHelper';
import { IContrato, IContratoErrors, IContratoForm } from '../models/Contrato';
import { ICliente } from '../models/Cliente';
import API from '../services/api';

class ContratoViewModel {

  public contratos: Ref<IContrato[]> = ref([]);
  public clientes: Ref<ICliente[]> = ref([]);
  public showForm: Ref<boolean> = ref(false);
  public form: Ref<IContratoForm> = ref(this.getDefaultForm());
  public errors: Ref<IContratoErrors> = ref({ cliente: '', data: '' });
  public apiError: Ref<string> = ref('');

  private getDefaultForm(): IContratoForm {

    return {
      cliente_id: '',
      data_inicio: DateUtils.getTodayISO(),
      status: 'Ativo'
    };
  }

  public get formValido() {
    return computed(() => this.validarForm());
  }

  public validarForm = (): boolean => {

    this.errors.value.cliente = !this.form.value.cliente_id ? 'Selecione um cliente' : '';
    this.errors.value.data = !this.form.value.data_inicio ? 'Data de início obrigatória' : '';

    return !this.errors.value.cliente && !this.errors.value.data;
  };

  private handleApiError = (error: any): void => {

    ApiHelper.mapErrorToFields(
      error,
      this.errors.value as unknown as Record<string, string>,
      this.apiError,
      { 'cliente': 'cliente', 'data': 'data' }
    );
  };

  public carregar = async (): Promise<void> => {

    try {
      const [resContratos, resClientes] = await Promise.all([
        API.getContratos(),
        API.getClientes()
      ]);

      this.contratos.value = Array.isArray(resContratos.data)
        ? resContratos.data
        : resContratos.data?.data || [];

      this.clientes.value = Array.isArray(resClientes.data)
        ? resClientes.data
        : resClientes.data?.data || [];

    } catch (error) {
      this.handleApiError(error);
    }
  };

  public salvar = async (): Promise<void> => {

    this.apiError.value = '';

    if (!this.validarForm()) return;

    try {
      await API.createContrato(this.form.value);

      this.showForm.value = false;
      this.form.value = this.getDefaultForm();

      await this.carregar();
    } catch (error) {
      this.handleApiError(error);
    }
  };

  public toggleStatus = async (contrato: IContrato): Promise<void> => {

    const novoStatus = contrato.status === 'Ativo' ? 'Cancelado' : 'Ativo';

    try {
      if (novoStatus === 'Ativo') {
        const { data } = await API.getContrato(contrato.id);

        if (!data.itens || data.itens.length === 0) {
          this.apiError.value = 'Contrato cancelado não pode ser editado.';
          alert(this.apiError.value);
          return;
        }
      }

      await API.updateContrato(contrato.id, { status: novoStatus });
      await this.carregar();

    } catch (error) {
      this.handleApiError(error);
    }
  };

  public remover = async (id: number): Promise<void> => {

    if (!confirm('Excluir contrato?')) return;

    try {
      await API.deleteContrato(id);
      await this.carregar();
    } catch (error) {
      this.handleApiError(error);
    }
  };
}

const contratoViewModel = new ContratoViewModel();

const { contratos, clientes, showForm, form, errors, apiError, formValido } = contratoViewModel;

const { validarForm, carregar, salvar, toggleStatus, remover } = contratoViewModel;

onMounted(carregar);

defineExpose({ DateUtils, CurrencyUtils });
</script>