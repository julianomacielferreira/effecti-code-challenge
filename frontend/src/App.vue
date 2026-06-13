<template>
  <div class="min-h-screen">
    <nav class="bg-slate-900 text-white px-6 py-4 flex gap-6">
      <h1 class="font-bold text-xl">Effecti ERP</h1>
      <router-link to="/clientes" class="hover:text-cyan-300">Clientes</router-link>
      <router-link to="/servicos" class="hover:text-cyan-300">Serviços</router-link>
      <router-link to="/contratos" class="hover:text-cyan-300">Contratos</router-link>
    </nav>
    <main class="p-6 max-w-6xl mx-auto">
      <router-view />
    </main>
  </div>
</template>
