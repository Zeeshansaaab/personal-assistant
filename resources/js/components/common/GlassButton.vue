<template>
  <button
    :type="type"
    :disabled="loading || disabled"
    :class="[
      'glass-strong px-4 sm:px-6 py-2 sm:py-3 rounded-xl sm:rounded-2xl font-semibold text-sm sm:text-base',
      'transition-all duration-300 transform',
      'hover:scale-105 active:scale-95',
      'disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100',
      variantClasses,
      sizeClasses,
      className
    ]"
    @click="handleClick"
  >
    <span v-if="loading" class="flex items-center justify-center gap-2">
      <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      {{ loadingText }}
    </span>
    <span v-else>
      <slot />
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary', // primary, secondary, outline
    validator: (value) => ['primary', 'secondary', 'outline'].includes(value)
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Loading...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  }
})

const variantClasses = computed(() => {
  const variants = {
    primary: 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50',
    secondary: 'dark:bg-white/20 dark:text-white dark:border-white/30 bg-slate-200 text-slate-800 border border-slate-300',
    outline: 'dark:bg-transparent dark:text-white dark:border-white/30 bg-transparent text-slate-800 border-2 border-slate-300'
  }
  return variants[props.variant] || variants.primary
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-6 py-3 text-base',
    lg: 'px-8 py-4 text-lg'
  }
  return sizes[props.size] || sizes.md
})

const emit = defineEmits(['click'])

const handleClick = (event) => {
  emit('click', event)
}
</script>

<style scoped>
.glass-strong {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(30px) saturate(200%);
  -webkit-backdrop-filter: blur(30px) saturate(200%);
}

.dark .glass-strong {
  background: rgba(0, 0, 0, 0.3);
}

body:not(.dark) .glass-strong {
  background: rgba(255, 255, 255, 0.8);
}
</style>
