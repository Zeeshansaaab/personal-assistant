<template>
  <div class="relative">
    <label
      v-if="label"
      class="block text-sm font-medium mb-2 dark:text-white/90 text-slate-700"
    >
      {{ label }}
      <span v-if="required" class="text-red-400">*</span>
    </label>
    <textarea
      v-if="type === 'textarea'"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :rows="rows || 4"
      :class="[
        'glass-strong w-full px-4 py-3 rounded-2xl border',
        'dark:border-white/20 border-slate-300',
        'dark:text-white text-slate-900 dark:placeholder-white/50 placeholder-slate-500',
        'focus:outline-none focus:ring-2 focus:ring-indigo-500/50',
        'focus:border-indigo-500 transition-all duration-300',
        'resize-none',
        error && 'border-red-400 focus:ring-red-400/50',
        className
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
    />
    <input
      v-else
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :class="[
        'glass-strong w-full px-4 py-3 rounded-2xl border',
        'dark:border-white/20 border-slate-300',
        'dark:text-white text-slate-900 dark:placeholder-white/50 placeholder-slate-500',
        'focus:outline-none focus:ring-2 focus:ring-indigo-500/50',
        'focus:border-indigo-500 transition-all duration-300',
        error && 'border-red-400 focus:ring-red-400/50',
        className
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
    />
    <p v-if="error" class="mt-1 text-sm text-red-400">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  className: {
    type: String,
    default: ''
  },
  rows: {
    type: Number,
    default: 4
  }
})

defineEmits(['update:modelValue', 'blur'])
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
  border-color: rgba(226, 232, 240, 0.8);
}
</style>
