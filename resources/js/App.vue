<template>
    <div id="app" v-if="isAuthenticated" class="min-h-screen">
        <nav class="glass-strong fixed top-0 left-0 right-0 z-50 rounded-b-3xl mx-2 sm:mx-4 mt-2">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 py-3 sm:py-4">
                <div class="flex items-center justify-between">
                    <router-link to="/" class="flex items-center gap-2 sm:gap-3">
                        <img :src="'/favicon.svg'" alt="Logo" class="w-6 h-6 sm:w-8 sm:h-8" />
                        <span class="text-lg sm:text-2xl font-bold gradient-text hidden sm:inline">Personal Life Manager</span>
                        <span class="text-lg font-bold gradient-text sm:hidden">PLM</span>
                    </router-link>
                    <div class="flex items-center gap-2 sm:gap-4 lg:gap-6">
                        <!-- Desktop Navigation Links - Only visible on md and above -->
                        <router-link
                            v-for="link in navLinks"
                            :key="`desktop-${link.path}`"
                            :to="link.path"
                            class="hidden md:block dark:text-white/80 dark:hover:text-white text-slate-700 hover:text-slate-900 transition-colors font-medium text-sm lg:text-base"
                            active-class="dark:text-white text-slate-900"
                        >
                            {{ link.label }}
                        </router-link>
                        <!-- Currency Selector -->
                        <div class="relative">
                            <select
                                v-model="selectedCurrency"
                                @change="handleCurrencyChange"
                                class="glass px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl dark:text-white/80 dark:hover:text-white text-slate-700 hover:text-slate-900 border-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer appearance-none bg-transparent text-xs sm:text-sm"
                            >
                                <option v-for="(currency, code) in currencies" :key="code" :value="code">
                                    {{ currency.symbol }} {{ code }}
                                </option>
                            </select>
                        </div>
                        <button
                            @click="toggleTheme"
                            class="glass p-1.5 sm:p-2 rounded-xl hover:scale-110 transition-transform duration-300"
                            :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                        >
                            <svg v-if="isDark" class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 dark:text-gray-300 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                        </button>
                        <button
                            @click="handleLogout"
                            class="glass px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl dark:text-white/80 dark:hover:text-white text-slate-700 hover:text-slate-900 transition-colors text-xs sm:text-sm"
                        >
                            <span class="hidden sm:inline">Logout</span>
                            <span class="sm:hidden">Out</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Mobile Navigation Links - Only visible on mobile (md:hidden) -->
            <div class="max-w-7xl mx-auto px-2 sm:px-6 pb-3 md:hidden overflow-hidden">
                <div class="flex items-center justify-around gap-1 sm:gap-2 pt-3 border-t dark:border-white/10 border-slate-200">
                    <router-link
                        v-for="link in navLinks"
                        :key="`mobile-${link.path}`"
                        :to="link.path"
                        class="dark:text-white/80 dark:hover:text-white text-slate-700 hover:text-slate-900 transition-colors font-medium text-xs px-1.5 sm:px-2 py-1 rounded-lg whitespace-nowrap flex-shrink-0"
                        active-class="dark:text-white text-slate-900 dark:bg-white/10 bg-slate-100"
                    >
                        {{ link.label }}
                    </router-link>
                </div>
            </div>
        </nav>
        <main class="main-content">
            <router-view />
        </main>
    </div>
    <router-view v-else />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from './composables/useAuth'
import { useCurrency, currencies } from './composables/useCurrency'

const router = useRouter()
const { isAuthenticated, logout, fetchUser, user } = useAuth()
const { updateCurrency, currentCurrency } = useCurrency()

const navLinks = [
    { path: '/', label: 'Dashboard' },
    { path: '/tasks', label: 'Tasks' },
    { path: '/lendings', label: 'Lendings' },
    { path: '/expenses', label: 'Expenses' },
    { path: '/groups', label: 'Groups' },
    { path: '/settings', label: 'Settings' },
]

const isDark = ref(false)
const selectedCurrency = ref('PKR')

const toggleTheme = () => {
  isDark.value = !isDark.value
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

const handleLogout = async () => {
    await logout()
}

onMounted(async () => {
    // Initialize theme first
    const savedTheme = localStorage.getItem('theme') || 'light'
    isDark.value = savedTheme === 'light'
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
    
    if (isAuthenticated.value) {
        await fetchUser()
        // Set currency from user or default to PKR
        selectedCurrency.value = user.value?.currency || 'PKR'
    }
})
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body styles moved to app.css for theme support */

.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.nav-links {
    display: flex;
    gap: 2rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.nav-link:hover,
.nav-link.router-link-active {
    background-color: rgba(255, 255, 255, 0.2);
}
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    cursor: pointer;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.modal-close:hover {
    color: #333;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #555;
}

.table tr:hover {
    background-color: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}

.badge-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.text-center {
    text-align: center;
}

.mt-2 {
    margin-top: 1rem;
}

.mb-2 {
    margin-bottom: 1rem;
}

.flex {
    display: flex;
}

.gap-2 {
    gap: 1rem;
}

.justify-between {
    justify-content: space-between;
}

.align-center {
    align-items: center;
}
</style>

