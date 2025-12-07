<template>
  <GlassCard class="relative overflow-hidden group">
    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
    <div class="relative z-10">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 rounded-2xl" :class="iconBgClass">
          <component :is="icon" class="w-6 h-6" :class="iconClass" />
        </div>
        <span v-if="trend" class="text-sm font-semibold" :class="trendClass">
          {{ trend }}
        </span>
      </div>
      <h3 class="text-2xl sm:text-3xl font-bold dark:text-white text-slate-900 mb-1">{{ value }}</h3>
      <p class="dark:text-white/70 text-slate-600 text-xs sm:text-sm">{{ label }}</p>
      <div v-if="subtitle" class="mt-2 text-xs dark:text-white/50 text-slate-500">{{ subtitle }}</div>
    </div>
  </GlassCard>
</template>

<script setup>
import GlassCard from './GlassCard.vue'
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: Object,
    required: true
  },
  iconBgClass: {
    type: String,
    default: 'bg-purple-500/20'
  },
  iconClass: {
    type: String,
    default: 'text-purple-300'
  },
  trend: {
    type: String,
    default: ''
  },
  trendType: {
    type: String,
    default: 'positive', // positive, negative, neutral
    validator: (value) => ['positive', 'negative', 'neutral'].includes(value)
  },
  subtitle: {
    type: String,
    default: ''
  }
})

const trendClass = computed(() => {
  const classes = {
    positive: 'text-green-400',
    negative: 'text-red-400',
    neutral: 'text-white/70'
  }
  return classes[props.trendType] || classes.neutral
})
</script>

