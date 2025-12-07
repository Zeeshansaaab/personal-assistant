<template>
  <div class="w-full pb-6 px-4 sm:px-6 bg-transparent">
    <Header title="Dashboard" subtitle="Overview of your personal life" />
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
      <StatsWidget
        :label="'Total Tasks'"
        :value="stats.tasks"
        :icon="TaskIcon"
        icon-bg-class="bg-blue-500/20"
        icon-class="text-blue-300"
      />
      <StatsWidget
        :label="'Active Lendings'"
        :value="stats.lendings"
        :icon="WalletIcon"
        icon-bg-class="bg-purple-500/20"
        icon-class="text-purple-300"
      />
      <StatsWidget
        :label="'This Month Expenses'"
        :value="formatCurrency(stats.expenses)"
        :icon="MoneyIcon"
        icon-bg-class="bg-green-500/20"
        icon-class="text-green-300"
      />
      <StatsWidget
        :label="'Active Groups'"
        :value="stats.groups"
        :icon="GroupIcon"
        icon-bg-class="bg-pink-500/20"
        icon-class="text-pink-300"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Recent Tasks</h3>
        <div v-if="recentTasks.length === 0" class="dark:text-white/50 text-slate-500 text-center py-8">
          No tasks yet
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="task in recentTasks"
            :key="task.id"
            class="p-4 rounded-2xl dark:bg-white/5 bg-slate-100/50 dark:border-white/10 border-slate-200 dark:hover:bg-white/10 hover:bg-slate-200/50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold dark:text-white text-slate-900">{{ task.title }}</p>
                <p class="text-sm dark:text-white/60 text-slate-600">{{ formatDate(task.due_date) }}</p>
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  task.is_completed ? 'bg-green-500/20 text-green-600 dark:text-green-300' : 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-300'
                ]"
              >
                {{ task.is_completed ? 'Done' : 'Pending' }}
              </span>
            </div>
          </div>
        </div>
        <router-link to="/tasks" class="block mt-4 text-center text-indigo-600 dark:text-indigo-300 hover:text-indigo-500 dark:hover:text-indigo-200 transition-colors">
          View All Tasks →
        </router-link>
      </GlassCard>

      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Recent Groups</h3>
        <div v-if="recentGroups.length === 0" class="dark:text-white/50 text-slate-500 text-center py-8">
          No groups yet
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="group in recentGroups"
            :key="group.id"
            class="p-4 rounded-2xl dark:bg-white/5 bg-slate-100/50 dark:border-white/10 border-slate-200 dark:hover:bg-white/10 hover:bg-slate-200/50 transition-colors cursor-pointer"
            @click="$router.push(`/groups/${group.id}`)"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold dark:text-white text-slate-900">{{ group.name }}</p>
                <p class="text-sm dark:text-white/60 text-slate-600">{{ group.members?.length || 0 }} members</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-600 dark:text-purple-300">
                Active
              </span>
            </div>
          </div>
        </div>
        <router-link to="/groups" class="block mt-4 text-center text-indigo-600 dark:text-indigo-300 hover:text-indigo-500 dark:hover:text-indigo-200 transition-colors">
          View All Groups →
        </router-link>
      </GlassCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Header from './layout/Header.vue'
import GlassCard from './common/GlassCard.vue'
import StatsWidget from './common/StatsWidget.vue'
import { useCurrency } from '../composables/useCurrency'

const { formatCurrency } = useCurrency()

const router = useRouter()

const stats = ref({
  tasks: 0,
  lendings: 0,
  expenses: '0.00',
  groups: 0
})

const recentTasks = ref([])
const recentGroups = ref([])

// Icons (simplified - you can use actual icon components)
const TaskIcon = { template: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>' }
const WalletIcon = { template: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>' }
const MoneyIcon = { template: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/></svg>' }
const GroupIcon = { template: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>' }

const formatDate = (dateString) => {
  if (!dateString) return 'No date'
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const loadStats = async () => {
  try {
      const [tasksRes, lendingsRes, expensesRes, groupsRes] = await Promise.all([
      axios.get('/tasks'),
      axios.get('/lendings'),
      axios.get('/expenses/summary/monthly', {
        params: { month: new Date().toISOString().slice(0, 7) }
      }),
      axios.get('/groups')
    ])

    stats.value.tasks = tasksRes.data.filter(t => !t.is_completed).length
    stats.value.lendings = lendingsRes.data.filter(l => !l.is_returned).length
    stats.value.expenses = parseFloat(expensesRes.data.total || 0).toFixed(2)
    stats.value.groups = groupsRes.data.length

    recentTasks.value = tasksRes.data.slice(0, 5)
    recentGroups.value = groupsRes.data.slice(0, 5)
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

onMounted(() => {
  loadStats()
})
</script>

