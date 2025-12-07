<template>
  <div class="w-full pb-6 px-4 sm:px-6 bg-transparent">
    <Header title="Settings" subtitle="Manage your preferences" />

    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Theme Settings -->
      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Appearance</h3>
        <div class="flex items-center justify-between">
          <div>
            <p class="dark:text-white/80 text-slate-700 font-medium">Dark Mode</p>
            <p class="text-sm dark:text-white/60 text-slate-500 mt-1">Switch between light and dark theme</p>
          </div>
          <button
            @click="toggleTheme"
            class="glass px-4 py-2 rounded-xl hover:scale-110 transition-transform duration-300"
            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          >
            <svg v-if="isDark" class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="w-6 h-6 dark:text-gray-300 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
              <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
          </button>
        </div>
      </GlassCard>

      <!-- Currency Settings -->
      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Currency</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium dark:text-white/80 text-slate-700 mb-2">
              Select Currency
            </label>
            <select
              v-model="selectedCurrency"
              @change="handleCurrencyChange"
              class="glass px-4 py-3 rounded-xl dark:text-white/80 dark:hover:text-white text-slate-700 hover:text-slate-900 border-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none bg-transparent w-full"
            >
              <option v-for="(currency, code) in currencies" :key="code" :value="code">
                {{ currency.symbol }} {{ code }} - {{ currency.name }}
              </option>
            </select>
            <p class="text-sm dark:text-white/60 text-slate-500 mt-2">
              All amounts will be displayed in the selected currency
            </p>
          </div>
        </div>
      </GlassCard>

      <!-- User Info -->
      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Account Information</h3>
        <div class="space-y-3">
          <div>
            <p class="text-sm dark:text-white/60 text-slate-500">Name</p>
            <p class="dark:text-white/80 text-slate-700 font-medium">{{ user?.name || 'N/A' }}</p>
          </div>
          <div v-if="user?.email">
            <p class="text-sm dark:text-white/60 text-slate-500">Email</p>
            <p class="dark:text-white/80 text-slate-700 font-medium">{{ user.email }}</p>
          </div>
          <div v-if="user?.mobile">
            <p class="text-sm dark:text-white/60 text-slate-500">Mobile</p>
            <p class="dark:text-white/80 text-slate-700 font-medium">{{ user.mobile }}</p>
          </div>
        </div>
      </GlassCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth'
import { useCurrency, currencies } from '../composables/useCurrency'
import Header from './layout/Header.vue'
import GlassCard from './common/GlassCard.vue'

const { user, fetchUser } = useAuth()
const { updateCurrency, currentCurrency } = useCurrency()

const isDark = ref(false)
const selectedCurrency = ref('PKR')

const toggleTheme = () => {
  const html = document.documentElement
  const body = document.body
  if (isDark.value) {
    html.classList.add('dark')
    body.classList.add('dark')
    html.setAttribute('data-theme', 'dark')
  } else {
    html.classList.remove('dark')
    body.classList.remove('dark')
    html.setAttribute('data-theme', 'light')
  }
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const handleCurrencyChange = async () => {
  const result = await updateCurrency(selectedCurrency.value)
  if (result.success) {
    // Currency updated successfully
    console.log('Currency updated to:', selectedCurrency.value)
  }
}

onMounted(async () => {
  // Initialize theme
  const savedTheme = localStorage.getItem('theme') || 'light'
  isDark.value = savedTheme === 'dark'
  const html = document.documentElement
  const body = document.body
  if (savedTheme === 'dark') {
    html.classList.add('dark')
    body.classList.add('dark')
    html.setAttribute('data-theme', 'dark')
    isDark.value = true
  } else {
    html.classList.remove('dark')
    body.classList.remove('dark')
    html.setAttribute('data-theme', 'light')
    isDark.value = false
  }
  
  if (user.value) {
    await fetchUser()
    selectedCurrency.value = user.value?.currency || 'PKR'
  }
})
</script>

