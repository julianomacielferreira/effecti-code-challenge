<template>
  <div v-if="contrato">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Contrato #{{ contrato.id }}</h2>
      <router-link to="/contratos" class="text-blue-600 hover:underline">← Voltar</router-link>
    </div>

    <div class="bg-white p-4 rounded shadow mb-6 grid grid-cols-2 md:grid-cols-6 gap-6">
      <div><strong>Cliente:</strong> {{ contrato.cliente?.nome }}</div>
      <div><strong>Início:</strong> {{ DateUtils.formatPTBR(contrato.data_inicio) }}</div>
      <div><strong>Status:</strong> {{ contrato.status }}</div>
      <div><strong>Subtotal:</strong> {{ CurrencyUtils.formatCurrency(subtotal) }}</div>
      <div><strong>Desconto:</strong> {{ CurrencyUtils.formatCurrency(desconto_total) }}</div>
      <div><strong>Total Mensal:</strong> {{ CurrencyUtils.formatCurrency(total) }}</div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-3">Itens do Contrato</h3>

      <div v-if="apiError" class="bg-red-50 border border-red-400 text-red-700 px-3 py-2 rounded mb-3 text-sm">
        {{ apiError }}
      </div>

      <form @submit.prevent="adicionarItem" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <div>
          <select v-model="item.servico_id" @change="onServicoChange" required class="border p-2 rounded w-full">
            <option value="">Serviço</option>
            <option v-for="servico in servicos" :key="servico.id" :value="servico.id">
              {{ servico.nome }} (R$ {{ CurrencyUtils.formatBRL(servico.valor_base_mensal) }})
            </option>
          </select>
          <p v-if="errors.servico" class="text-red-600 text-xs mt-1">{{ errors.servico }}</p>
        </div>

        <div>
          <input v-model.number="item.quantidade" type="number" min="1" placeholder="Qtd" required
            class="border p-2 rounded w-full" />
          <p v-if="errors.quantidade" class="text-red-600 text-xs mt-1">{{ errors.quantidade }}</p>
        </div>

        <div>
          <input :value="valorItemMask" @input="onValorItemInput" type="text" inputmode="decimal"
            placeholder="Valor unitário" required class="border p-2 rounded w-full" />
          <p v-if="errors.valor" class="text-red-600 text-xs mt-1">{{ errors.valor }}</p>
        </div>

        <button class="bg-cyan-600 text-white rounded px-4 h-[42px]">Adicionar</button>
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
          <tr v-if="(contrato.itens || []).length === 0">
            <td colspan="4" class="p-4 text-center text-gray-500">
              Nenhum item adicionado a este contrato.
            </td>
          </tr>
          <tr v-for="itemObj in contrato.itens || []" :key="itemObj.id" class="border-t">
            <td class="p-2">{{ itemObj.servico?.nome || itemObj.servico_id }}</td>
            <td>{{ itemObj.quantidade }}</td>
            <td>{{ CurrencyUtils.formatCurrency(itemObj.valor_unitario || 0) }}</td>
            <td>{{ CurrencyUtils.formatCurrency((itemObj.quantidade || 0) * (itemObj.valor_unitario || 0)) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 p-3 bg-slate-50 rounded">
        <p class="text-sm text-gray-600">
          <strong>Regras de negócio aplicadas:</strong> {{ regras_aplicadas.join(', ') || 'Nenhuma' }}
        </p>
      </div>
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
import { IContrato, IContratoItemErrors, IContratoItemForm } from '../models/Contrato';
import { IServico } from '../models/Servico';
import API from '../services/api';

const props = defineProps<{ id: string | number }>();

class ContratoItensViewModel {

  private contratoId: string | number;

  public contrato: Ref<IContrato | null> = ref(null);
  public servicos: Ref<IServico[]> = ref([]);
  public regras_aplicadas: Ref<string[]> = ref([]);
  public item: Ref<IContratoItemForm> = ref({ servico_id: '', quantidade: 1, valor_unitario: null });
  public apiError: Ref<string> = ref('');
  public errors: Ref<IContratoItemErrors> = ref({ servico: '', quantidade: '', valor: '' });

  constructor(contratoId: string | number) {
    this.contratoId = contratoId;
  }

  public get valorItemMask() {
    return computed(() => CurrencyUtils.formatBRL(this.item.value.valor_unitario));
  }

  public get total() {
    return computed(() => this.contrato.value?.totais?.total || 0);
  }

  public get subtotal() {
    return computed(() => this.contrato.value?.totais?.subtotal || 0);
  }

  public get desconto_total() {
    return computed(() => this.contrato.value?.totais?.desconto_total || 0);
  }

  public onValorItemInput = (event: Event): void => {

    const target = event.target as HTMLInputElement;
    const value = CurrencyUtils.parseFromMask(target.value);

    this.item.value.valor_unitario = value;
    target.value = value != null ? CurrencyUtils.formatBRL(value) : '';
  };

  public onServicoChange = (): void => {
    const servico = this.servicos.value.find(s => s.id == this.item.value.servico_id);
    if (servico) {
      this.item.value.valor_unitario = Number(servico.valor_base_mensal);
    }
  };

  private handleApiError = (error: any): void => {
    ApiHelper.mapErrorToFields(
      error,
      this.errors.value as unknown as Record<string, string>,
      this.apiError,
      { 'serviço': 'servico', 'servico': 'servico', 'quantidade': 'quantidade', 'valor': 'valor' }
    );
  };

  public carregar = async (): Promise<void> => {

    try {

      const [resContrato, resServicos] = await Promise.all([
        API.getContrato(this.contratoId),
        API.getServicos()
      ]);

      this.contrato.value = resContrato.data;
      this.regras_aplicadas.value = resContrato.data?.totais?.regras_aplicadas || [];
      this.servicos.value = Array.isArray(resServicos.data) ? resServicos.data : resServicos.data?.data || [];

    } catch (error) {
      this.handleApiError(error);
    }

  };

  public adicionarItem = async (): Promise<void> => {

    this.apiError.value = '';
    this.errors.value = { servico: '', quantidade: '', valor: '' };

    if (!this.item.value.servico_id) {
      this.errors.value.servico = 'Selecione um serviço';
      return;
    }

    if (!this.item.value.quantidade || this.item.value.quantidade < 1) {
      this.errors.value.quantidade = 'Quantidade inválida';
      return;
    }

    if (this.item.value.valor_unitario == null) {
      const servico = this.servicos.value.find(s => s.id == this.item.value.servico_id);
      this.item.value.valor_unitario = servico ? Number(servico.valor_base_mensal) : 0;
    }

    try {

      await API.addItem(this.contratoId, this.item.value);
      this.item.value = { servico_id: '', quantidade: 1, valor_unitario: null };
      await this.carregar();

    } catch (error) {
      this.handleApiError(error);
    }
  };
}

const contratoItensViewModel = new ContratoItensViewModel(props.id);

const { contrato, servicos, regras_aplicadas, item, apiError, errors, valorItemMask, total, subtotal, desconto_total } = contratoItensViewModel;

const { onValorItemInput, onServicoChange, carregar, adicionarItem } = contratoItensViewModel;

onMounted(carregar);

defineExpose({ DateUtils, CurrencyUtils });
</script>