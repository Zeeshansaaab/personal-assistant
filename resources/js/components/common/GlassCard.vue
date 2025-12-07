<template>
  <div
    :class="[
      'glass rounded-2xl sm:rounded-3xl p-4 sm:p-6 transition-all duration-300',
      hoverable && 'hover:scale-[1.02] hover:shadow-2xl cursor-pointer',
      className
    ]"
    @click="hoverable && $emit('click')"
  >
    <slot />
  </div>
</template>

<script setup>
defineProps({
  hoverable: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  }
})

defineEmits(['click'])
</script>

