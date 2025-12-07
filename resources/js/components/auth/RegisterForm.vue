<template>
  <div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden auth-background">
    <div class="relative z-10 w-full max-w-md">
      <!-- Compact Header -->
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-white mb-1">Create Account</h1>
        <p class="text-slate-400 text-sm">Get started with your personal life manager</p>
      </div>

      <!-- Register Card -->
      <div class="glass-card rounded-2xl p-6 shadow-2xl">
        <form @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wide">
              Full Name <span class="text-red-400">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <input
                v-model="form.name"
                type="text"
                placeholder="Enter your full name"
                required
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wide">
              Email Address
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>
              <input
                v-model="form.email"
                type="email"
                placeholder="Enter email (optional)"
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wide">
              Mobile Number
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
              </div>
              <input
                v-model="form.mobile"
                type="tel"
                placeholder="Enter mobile (optional)"
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
              />
            </div>
            <p class="mt-1 text-xs text-slate-500">At least email or mobile is required</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wide">
              Password <span class="text-red-400">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
              </div>
              <input
                v-model="form.password"
                type="password"
                placeholder="Create password (min 8 chars)"
                required
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wide">
              Confirm Password <span class="text-red-400">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Confirm password"
                required
                class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-800/50 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
              />
            </div>
          </div>

          <div v-if="error" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30">
            <div class="flex items-center">
              <svg class="w-4 h-4 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
              <p class="text-red-300 text-xs font-medium">{{ error }}</p>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold text-sm rounded-lg shadow-lg shadow-indigo-500/50 hover:shadow-indigo-500/70 transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center justify-center"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating account...
            </span>
            <span v-else>Create Account</span>
          </button>

          <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-xs">
              <span class="px-3 bg-slate-900/50 text-slate-500">Already have an account?</span>
            </div>
          </div>

          <router-link
            to="/login"
            class="block w-full py-2.5 px-4 bg-slate-800/50 border border-slate-700 hover:border-slate-600 text-white font-semibold text-sm rounded-lg transition-all duration-200 text-center hover:bg-slate-800/70"
          >
            Sign in instead
          </router-link>
        </form>
      </div>

      <!-- Compact Footer -->
      <p class="text-center text-slate-500 text-xs mt-6">
        By creating an account, you agree to our Terms and Privacy Policy
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { register } = useAuth()

const form = ref({
  name: '',
  email: '',
  mobile: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref('')

const handleRegister = async () => {
  if (!form.value.email && !form.value.mobile) {
    error.value = 'Please provide either email or mobile number'
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  if (form.value.password.length < 8) {
    error.value = 'Password must be at least 8 characters long'
    return
  }

  loading.value = true
  error.value = ''

  const result = await register(form.value)

  if (result.success) {
    router.push('/')
  } else {
    error.value = result.error || 'Registration failed. Please try again.'
  }

  loading.value = false
}
</script>

<style scoped>
.auth-background {
  background: linear-gradient(135deg, #0f172a 0%, #581c87 50%, #0f172a 100%);
  position: relative;
}

.auth-background::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 40% 20%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
  pointer-events: none;
}

.auth-background::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: 
    repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(148, 163, 184, 0.03) 2px, rgba(148, 163, 184, 0.03) 4px);
  pointer-events: none;
}

.glass-card {
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(148, 163, 184, 0.1);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}
</style>
