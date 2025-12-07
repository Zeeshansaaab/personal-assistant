<template>
  <header class="glass-strong rounded-2xl sm:rounded-3xl p-4 sm:p-6 mb-6 sm:mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex-1">
        <h1 class="text-2xl sm:text-3xl font-bold gradient-text">{{ title }}</h1>
        <p v-if="subtitle" class="dark:text-white/70 text-slate-600 mt-1 text-sm sm:text-base">{{ subtitle }}</p>
      </div>
      <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto">
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>

