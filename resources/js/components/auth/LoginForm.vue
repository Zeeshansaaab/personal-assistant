<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <div class="w-full max-w-md">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
        <p class="text-slate-300">Sign in to your account</p>
      </div>

      <!-- Login Card -->
      <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email/Mobile Input -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">
              Email or Mobile
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <input
                v-model="form.email"
                type="text"
                placeholder="Enter email or mobile"
                required
                class="w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
              />
            </div>
          </div>

          <!-- Password Input -->
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
              </div>
              <input
                v-model="form.password"
                type="password"
                placeholder="Enter password"
                required
                class="w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
              />
            </div>
          </div>

          <!-- Remember & Forgot -->
          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center cursor-pointer">
              <input type="checkbox" class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-indigo-600 focus:ring-indigo-500">
              <span class="ml-2 text-slate-400">Remember me</span>
            </label>
            <a href="#" class="text-indigo-400 hover:text-indigo-300 transition-colors">
              Forgot password?
            </a>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="p-4 rounded-lg bg-red-500/10 border border-red-500/30">
            <p class="text-red-300 text-sm">{{ error }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/50 hover:shadow-indigo-500/70 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center justify-center"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>

          <!-- Divider -->
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-slate-800/80 text-slate-400">New here?</span>
            </div>
          </div>

          <!-- Register Link -->
          <router-link
            to="/register"
            class="block w-full py-3 px-4 bg-slate-700/50 border border-slate-600 hover:border-slate-500 text-white font-semibold rounded-lg transition-all duration-200 text-center hover:bg-slate-700/70"
          >
            Create an account
          </router-link>
        </form>
      </div>

      <!-- Footer -->
      <p class="text-center text-slate-500 text-xs mt-6">
        By signing in, you agree to our Terms and Privacy Policy
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { login } = useAuth()

const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  const isEmail = form.value.email.includes('@')
  const credentials = {
    password: form.value.password
  }

  if (isEmail) {
    credentials.email = form.value.email
  } else {
    credentials.mobile = form.value.email
  }

  const result = await login(credentials)

  if (result.success) {
    router.push('/')
  } else {
    error.value = result.error || 'Invalid credentials. Please try again.'
  }

  loading.value = false
}
</script>
