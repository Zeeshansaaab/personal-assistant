<template>
  <div class="w-full pb-6 px-4 sm:px-6 bg-transparent">
    <Header title="Expense Tracker" subtitle="Track and analyze your expenses">
      <template #actions>
        <GlassButton @click="openModal" variant="primary">
          + Add Expense
        </GlassButton>
      </template>
    </Header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Monthly Summary</h3>
        <div class="mb-4">
          <input
            v-model="selectedMonth"
            type="month"
            @change="fetchSummary"
            class="px-4 py-2 rounded-xl dark:bg-white/10 bg-slate-100 dark:text-white text-slate-900 border dark:border-white/20 border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>
        <div v-if="summary" class="space-y-4">
          <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-indigo-500/20 to-purple-500/20">
            <span class="dark:text-white/90 text-slate-700 font-medium">Total Spending</span>
            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">
              {{ formatCurrency(summary.total || 0) }}
            </span>
          </div>
          <div v-if="summary.by_category && summary.by_category.length > 0" class="space-y-2">
            <h4 class="text-sm font-semibold dark:text-white/90 text-slate-700 mb-2">By Category:</h4>
            <div
              v-for="item in summary.by_category"
              :key="item.category"
              class="flex items-center justify-between p-3 rounded-lg dark:bg-white/5 bg-slate-50"
            >
              <span class="dark:text-white/80 text-slate-700">{{ item.category }}</span>
              <span class="font-semibold text-indigo-600 dark:text-indigo-300">
                {{ formatCurrency(item.total) }}
              </span>
            </div>
          </div>
        </div>
      </GlassCard>

      <GlassCard>
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-4">Spending Trend</h3>
        <div class="chart-container">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </GlassCard>
    </div>

    <GlassCard>
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold dark:text-white text-slate-900">Expense List</h3>
        <select
          v-model="filterCategory"
          @change="fetchExpenses"
          class="px-4 py-2 rounded-xl dark:bg-white/10 bg-slate-100 dark:text-white text-slate-900 border dark:border-white/20 border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>

      <div v-if="expenses.length === 0" class="text-center py-16">
        <p class="dark:text-white/70 text-slate-600 mb-6">No expenses yet. Add your first expense!</p>
        <GlassButton @click="openModal" variant="primary">
          Add Expense
        </GlassButton>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <GlassCard
          v-for="expense in expenses"
          :key="expense.id"
          hoverable
          class="cursor-pointer"
          @click="editExpense(expense)"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <span
                class="px-2 py-1 rounded-lg text-xs font-medium dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700"
              >
                {{ expense.category }}
              </span>
              <h3 class="text-xl font-bold text-indigo-600 dark:text-indigo-300 mt-2">
                {{ formatCurrency(expense.amount) }}
              </h3>
            </div>
            <span class="text-xs dark:text-white/60 text-slate-600">
              {{ formatDate(expense.expense_date) }}
            </span>
          </div>
          <p
            v-if="expense.description"
            class="text-sm dark:text-white/60 text-slate-600 mb-3"
          >
            {{ expense.description }}
          </p>
          <div class="flex gap-2 pt-3 border-t dark:border-white/10 border-slate-200">
            <GlassButton
              @click.stop="editExpense(expense)"
              variant="outline"
              class="flex-1 text-xs"
            >
              Edit
            </GlassButton>
            <GlassButton
              @click.stop="deleteExpense(expense.id)"
              variant="outline"
              class="flex-1 text-xs"
              style="border-color: rgb(239, 68, 68); color: rgb(239, 68, 68);"
            >
              Delete
            </GlassButton>
          </div>
        </GlassCard>
      </div>
    </GlassCard>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <GlassCard class="max-w-md w-full p-4 sm:p-6 lg:p-8 mx-4">
        <h3 class="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-4 sm:mb-6">
          {{ editingExpense ? 'Edit Expense' : 'Add New Expense' }}
        </h3>
        <form @submit.prevent="saveExpense" class="space-y-4">
          <div>
            <label class="block text-sm font-medium dark:text-white/90 text-slate-700 mb-2">
              Category *
            </label>
            <input
              v-model="form.category"
              type="text"
              list="categories"
              placeholder="e.g., Food, Transport"
              class="glass-strong w-full px-4 py-3 rounded-2xl border dark:border-white/20 border-slate-300 dark:text-white text-slate-900 dark:placeholder-white/50 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all duration-300"
              required
            />
            <datalist id="categories">
              <option v-for="cat in commonCategories" :key="cat" :value="cat"></option>
            </datalist>
            <div class="flex flex-wrap gap-2 mt-2">
              <button
                v-for="cat in commonCategories"
                :key="cat"
                type="button"
                @click="form.category = cat"
                :class="[
                  'px-3 py-1 rounded-lg text-xs transition-all',
                  form.category === cat
                    ? 'bg-indigo-500 text-white'
                    : 'dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700',
                ]"
              >
                {{ cat }}
              </button>
            </div>
          </div>
          <GlassInput
            v-model="form.amount"
            label="Amount *"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            required
          />
          <GlassInput
            v-model="form.description"
            label="Description"
            placeholder="Optional description"
            type="textarea"
          />
          <GlassInput
            v-model="form.expense_date"
            label="Date *"
            type="date"
            required
          />
          <div class="flex gap-4 pt-4">
            <GlassButton type="button" @click="closeModal" variant="secondary" class="flex-1">
              Cancel
            </GlassButton>
            <GlassButton type="submit" variant="primary" class="flex-1">
              Save Expense
            </GlassButton>
          </div>
        </form>
      </GlassCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import Header from './layout/Header.vue'
import GlassCard from './common/GlassCard.vue'
import GlassInput from './common/GlassInput.vue'
import GlassButton from './common/GlassButton.vue'
import { useCurrency } from '../composables/useCurrency'

const { formatCurrency } = useCurrency()

Chart.register(...registerables)

const expenses = ref([])
const summary = ref(null)
const selectedMonth = ref(new Date().toISOString().slice(0, 7))
const filterCategory = ref('')
const categories = ref([])
const showModal = ref(false)
const editingExpense = ref(null)
const chart = ref(null)
const chartCanvas = ref(null)
const form = ref({
  category: '',
  amount: '',
  description: '',
  expense_date: new Date().toISOString().slice(0, 10),
})
const commonCategories = [
  'Food',
  'Transport',
  'Shopping',
  'Bills',
  'Entertainment',
  'Health',
  'Education',
  'Travel',
  'Other'
]

const fetchExpenses = async () => {
  try {
    const params = {}
    if (filterCategory.value) {
      params.category = filterCategory.value
    }
    const response = await axios.get('/expenses', { params })
    expenses.value = response.data
    extractCategories()
  } catch (error) {
    console.error('Error fetching expenses:', error)
    alert('Failed to fetch expenses')
  }
}

const fetchSummary = async () => {
  try {
    const response = await axios.get('/expenses/summary/monthly', {
      params: { month: selectedMonth.value }
    })
    summary.value = response.data
  } catch (error) {
    console.error('Error fetching summary:', error)
  }
}

const fetchChartData = async () => {
  try {
    const response = await axios.get('/expenses/chart/data', {
      params: { months: 6 }
    })
    await nextTick()
    renderChart(response.data)
  } catch (error) {
    console.error('Error fetching chart data:', error)
  }
}

const renderChart = (data) => {
  if (!chartCanvas.value) return

  if (chart.value) {
    chart.value.destroy()
  }

  const isDark = document.documentElement.classList.contains('dark')
  const textColor = isDark ? 'rgba(241, 245, 249, 0.8)' : 'rgba(0, 0, 0, 0.8)'
  const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'

  chart.value = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: data.map(item => item.month.slice(5)),
      datasets: [{
        label: 'Total Spending',
        data: data.map(item => parseFloat(item.total)),
        borderColor: 'rgb(102, 126, 234)',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          labels: {
            color: textColor
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            color: textColor,
            callback: function(value) {
              return formatCurrency(value)
            }
          },
          grid: {
            color: gridColor
          }
        },
        x: {
          ticks: {
            color: textColor
          },
          grid: {
            color: gridColor
          }
        }
      }
    }
  })
}

const extractCategories = () => {
  const cats = [...new Set(expenses.value.map(e => e.category))]
  categories.value = cats.sort()
}

const openModal = () => {
  editingExpense.value = null
  resetForm()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingExpense.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    category: '',
    amount: '',
    description: '',
    expense_date: new Date().toISOString().slice(0, 10),
  }
}

const editExpense = (expense) => {
  editingExpense.value = expense
  form.value = {
    category: expense.category,
    amount: expense.amount,
    description: expense.description || '',
    expense_date: expense.expense_date,
  }
  showModal.value = true
}

const saveExpense = async () => {
  try {
    if (editingExpense.value) {
      await axios.put(`/expenses/${editingExpense.value.id}`, form.value)
    } else {
      await axios.post('/expenses', form.value)
    }
    closeModal()
    fetchExpenses()
    fetchSummary()
    fetchChartData()
  } catch (error) {
    console.error('Error saving expense:', error)
    alert('Failed to save expense')
  }
}

const deleteExpense = async (id) => {
  if (!confirm('Are you sure you want to delete this expense?')) return
  try {
    await axios.delete(`/expenses/${id}`)
    fetchExpenses()
    fetchSummary()
    fetchChartData()
  } catch (error) {
    console.error('Error deleting expense:', error)
    alert('Failed to delete expense')
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchExpenses()
  fetchSummary()
  fetchChartData()
})
</script>

<style scoped>
.chart-container {
  height: 300px;
  margin-top: 1rem;
}
</style>
