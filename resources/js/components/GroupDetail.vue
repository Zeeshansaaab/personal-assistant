<template>
  <div class="w-full pb-6 px-4 sm:px-6 bg-transparent">
    <Header :title="group?.name || 'Group Details'" :subtitle="group?.description || ''">
      <template #actions>
        <GlassButton @click="showAddExpenseModal = true" variant="primary">
          + Add Expense
        </GlassButton>
      </template>
    </Header>

    <div v-if="loading" class="text-center py-16">
      <p class="dark:text-white/70 text-slate-600">Loading group details...</p>
    </div>

    <div v-else-if="group">
      <GlassCard class="mb-4 p-3">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-xl bg-indigo-500/20">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold dark:text-white text-slate-900 truncate">{{ group.name }}</h2>
            <p v-if="group.description" class="text-xs dark:text-white/60 text-slate-600 mt-0.5 truncate">
              {{ group.description }}
            </p>
            <span class="text-xs dark:text-white/70 text-slate-600 mt-1 inline-block">
              {{ group.members?.length || 0 }} members
            </span>
          </div>
        </div>
      </GlassCard>

      <div class="flex gap-2 mb-4">
        <button
          @click="activeTab = 'expenses'"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all',
            activeTab === 'expenses'
              ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg'
              : 'dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700',
          ]"
        >
          Expenses
        </button>
        <button
          @click="activeTab = 'tasks'"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-all',
            activeTab === 'tasks'
              ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg'
              : 'dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700',
          ]"
        >
          Tasks
        </button>
      </div>

      <div v-if="activeTab === 'expenses'">
        <!-- User Balance Summary -->
        <GlassCard class="mb-4 p-4">
          <h3 class="text-base font-bold dark:text-white text-slate-900 mb-3">Your Balance</h3>
          <div class="grid grid-cols-3 gap-3">
            <div class="text-center">
              <p class="text-sm dark:text-white/60 text-slate-500 mb-1">You Paid</p>
              <p 
                :class="[
                  'font-bold text-green-600 dark:text-green-400',
                  getBalanceClass(userBalance.paid)
                ]"
              >
                {{ formatCurrency(userBalance.paid) }}
              </p>
            </div>
            <div class="text-center">
              <p class="text-sm dark:text-white/60 text-slate-500 mb-1">You Owe</p>
              <p 
                :class="[
                  'font-bold text-red-600 dark:text-red-400',
                  getBalanceClass(userBalance.owed)
                ]"
              >
                {{ formatCurrency(userBalance.owed) }}
              </p>
            </div>
            <div class="text-center">
              <p class="text-sm dark:text-white/60 text-slate-500 mb-1">Net</p>
              <p
                :class="[
                  'font-bold',
                  userBalance.net >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
                  getBalanceClass(userBalance.net)
                ]"
              >
                {{ userBalance.net >= 0 ? '+' : '' }}{{ formatCurrency(userBalance.net) }}
              </p>
            </div>
          </div>
        </GlassCard>

        <div v-if="splitExpenses.length === 0" class="text-center py-16">
          <GlassCard class="max-w-md mx-auto">
            <p class="dark:text-white/70 text-slate-600 mb-6">No split expenses yet. Add your first expense!</p>
            <GlassButton @click="showAddExpenseModal = true" variant="primary">
              Add Expense
            </GlassButton>
          </GlassCard>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <GlassCard
            v-for="expense in splitExpenses"
            :key="`expense-${expense.id}`"
            class="p-4"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1 pr-2">
                <h3 class="text-base font-semibold dark:text-white text-slate-900 mb-1.5">
                  {{ expense.description || 'Split Expense' }}
                </h3>
                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-300">
                  {{ formatCurrency(expense.amount) }}
                </p>
                <p v-if="expense.payer" class="text-xs dark:text-white/60 text-slate-500 mt-1">
                  Paid by: {{ expense.payer.name || expense.payer.email }}
                </p>
              </div>
            </div>

            <div v-if="expense.splits && expense.splits.length > 0" class="space-y-1.5 pt-3 border-t dark:border-white/10 border-slate-200">
              <p class="text-xs font-medium dark:text-white/60 text-slate-500 mb-1.5">Split among:</p>
              <div
                v-for="(split, index) in expense.splits"
                :key="`split-${expense.id}-${split.user_id || index}`"
                class="flex items-center justify-between px-2 py-1.5 rounded-lg dark:bg-white/5 bg-slate-50"
              >
                <span class="text-sm dark:text-white/80 text-slate-700">{{ split.user_name }}</span>
                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">
                  {{ formatCurrency(split.amount) }}
                </span>
              </div>
            </div>
          </GlassCard>
        </div>
      </div>

      <div v-else class="text-center py-16">
        <GlassCard class="max-w-md mx-auto">
          <p class="dark:text-white/70 text-slate-600">Group tasks coming soon</p>
        </GlassCard>
      </div>
    </div>

    <!-- Add Expense Modal -->
    <div
      v-if="showAddExpenseModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      @click.self="showAddExpenseModal = false"
    >
      <GlassCard class="max-w-md w-full p-4 sm:p-6 lg:p-8 mx-4">
        <h3 class="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-4 sm:mb-6">Add Expense to Group</h3>
        <form @submit.prevent="addExpenseToGroup" class="space-y-4">
          <GlassInput
            v-model="expenseForm.category"
            label="Category *"
            placeholder="e.g., Food, Transport"
            required
          />
          <GlassInput
            v-model="expenseForm.amount"
            label="Amount *"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            required
          />
          <GlassInput
            v-model="expenseForm.description"
            label="Description"
            placeholder="Optional description"
            type="textarea"
          />
          <GlassInput
            v-model="expenseForm.expense_date"
            label="Date *"
            type="date"
            required
          />
          
          <!-- Who Paid -->
          <div class="space-y-2">
            <label class="block text-sm font-medium dark:text-white/80 text-slate-700">Who Paid? *</label>
            <select
              v-model="expenseForm.paid_by"
              class="w-full glass px-4 py-2.5 rounded-xl dark:text-white text-slate-900 dark:bg-white/10 bg-white/80 border border-slate-300 dark:border-white/20 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            >
              <option v-for="member in group?.members" :key="member.id" :value="member.id">
                {{ member.name || member.email }} {{ member.id === user?.id ? '(You)' : '' }}
              </option>
            </select>
          </div>
          
          <!-- Split Options -->
          <div class="space-y-3">
            <label class="block text-sm font-medium dark:text-white/80 text-slate-700">How to Split? *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <button
                type="button"
                @click="expenseForm.splitType = 'equal'"
                :class="[
                  'px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-left',
                  expenseForm.splitType === 'equal'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg'
                    : 'dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700 hover:dark:bg-white/20 hover:bg-slate-200',
                ]"
              >
                <div class="font-semibold">Split Equally</div>
                <div class="text-xs opacity-90 mt-0.5">All members split equally</div>
              </button>
              <button
                type="button"
                @click="expenseForm.splitType = 'one_person'"
                :class="[
                  'px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-left',
                  expenseForm.splitType === 'one_person'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg'
                    : 'dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700 hover:dark:bg-white/20 hover:bg-slate-200',
                ]"
              >
                <div class="font-semibold">One Person Owes All</div>
                <div class="text-xs opacity-90 mt-0.5">One person owes full amount</div>
              </button>
              <button
                type="button"
                @click="expenseForm.splitType = 'custom'"
                :class="[
                  'px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-left col-span-full',
                  expenseForm.splitType === 'custom'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg'
                    : 'dark:bg-white/10 bg-slate-100 dark:text-white/80 text-slate-700 hover:dark:bg-white/20 hover:bg-slate-200',
                ]"
              >
                <div class="font-semibold">Custom Split</div>
                <div class="text-xs opacity-90 mt-0.5">Set custom amounts for each person</div>
              </button>
            </div>
            
            <!-- One Person Owes All Selector -->
            <div v-if="expenseForm.splitType === 'one_person' && group?.members" class="mt-3 p-3 rounded-xl dark:bg-white/5 bg-slate-50">
              <label class="block text-xs font-medium dark:text-white/70 text-slate-600 mb-2">Who owes this amount?</label>
              <select
                v-model="expenseForm.owes_all_user_id"
                class="w-full glass px-3 py-2 rounded-lg dark:text-white text-slate-900 dark:bg-white/10 bg-white border border-slate-300 dark:border-white/20 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
              >
                <option v-for="member in group.members" :key="member.id" :value="member.id">
                  {{ member.name || member.email }}
                </option>
              </select>
            </div>
            
            <!-- Custom Split Inputs -->
            <div v-if="expenseForm.splitType === 'custom' && group?.members" class="space-y-2 mt-3 p-3 rounded-xl dark:bg-white/5 bg-slate-50">
              <p class="text-xs font-medium dark:text-white/70 text-slate-600 mb-2">Enter amount for each member:</p>
              <div
                v-for="member in group.members"
                :key="member.id"
                class="flex items-center justify-between px-2 py-1.5 rounded-lg dark:bg-white/5 bg-white"
              >
                <span class="text-sm dark:text-white/80 text-slate-700">{{ member.name || member.email }}</span>
                <input
                  v-model.number="expenseForm.customSplits[member.id]"
                  type="number"
                  step="0.01"
                  min="0"
                  :placeholder="formatCurrency(0)"
                  class="glass px-2 py-1 rounded-lg dark:text-white/80 text-slate-700 w-28 text-right text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>
              <p v-if="expenseForm.amount" class="text-xs dark:text-white/60 text-slate-500 mt-2 text-right">
                Total: {{ formatCurrency(Object.values(expenseForm.customSplits).reduce((a, b) => (a || 0) + (b || 0), 0)) }} / 
                {{ formatCurrency(parseFloat(expenseForm.amount) || 0) }}
              </p>
            </div>
          </div>
          
          <div class="flex gap-4 pt-4">
            <GlassButton type="button" @click="showAddExpenseModal = false" variant="secondary" class="flex-1">
              Cancel
            </GlassButton>
            <GlassButton type="submit" variant="primary" class="flex-1" :loading="addingExpense">
              Add Expense
            </GlassButton>
          </div>
        </form>
      </GlassCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Header from './layout/Header.vue'
import GlassCard from './common/GlassCard.vue'
import GlassInput from './common/GlassInput.vue'
import GlassButton from './common/GlassButton.vue'
import { useCurrency } from '../composables/useCurrency'
import { useAuth } from '../composables/useAuth'

const { formatCurrency } = useCurrency()
const { user } = useAuth()

const route = useRoute()
const router = useRouter()
const groupId = route.params.id

const group = ref(null)
const splitExpenses = ref([])
const loading = ref(true)
const activeTab = ref('expenses')
const showAddExpenseModal = ref(false)
const addingExpense = ref(false)
const userBalance = ref({ paid: 0, owed: 0, net: 0 })
const expenseForm = ref({
  category: '',
  amount: '',
  description: '',
  expense_date: new Date().toISOString().slice(0, 10),
  paid_by: null, // User ID who paid
  splitType: 'equal', // 'equal', 'one_person', 'custom'
  owes_all_user_id: null, // For one_person split type
  customSplits: {}, // { userId: amount }
})

const loadGroupData = async () => {
  try {
    loading.value = true
    const [groupRes, expensesRes, balanceRes] = await Promise.all([
      axios.get(`/groups/${groupId}`),
      axios.get(`/groups/${groupId}/expenses`),
      axios.get(`/groups/${groupId}/balance`),
    ])
    group.value = groupRes.data
    // Ensure we have an array and filter out any duplicates
    const expenses = expensesRes.data || []
    const filteredExpenses = expenses.filter((expense, index, self) => 
      index === self.findIndex(e => e.id === expense.id)
    )
    splitExpenses.value = filteredExpenses
    userBalance.value = balanceRes.data.user_balance || { paid: 0, owed: 0, net: 0 }
  } catch (error) {
    console.error('Error loading group data:', error)
    alert('Failed to load group data')
  } finally {
    loading.value = false
  }
}

const addExpenseToGroup = async () => {
  if (!expenseForm.value.category || !expenseForm.value.amount || !expenseForm.value.paid_by) {
    alert('Please fill in all required fields')
    return
  }

  // Validate one person owes all
  if (expenseForm.value.splitType === 'one_person' && !expenseForm.value.owes_all_user_id) {
    alert('Please select who owes this amount')
    return
  }

  // Validate custom split
  if (expenseForm.value.splitType === 'custom') {
    const totalSplit = Object.values(expenseForm.value.customSplits).reduce((a, b) => (a || 0) + (b || 0), 0)
    const totalAmount = parseFloat(expenseForm.value.amount)
    if (Math.abs(totalSplit - totalAmount) > 0.01) {
      alert(`Custom split total (${formatCurrency(totalSplit)}) must equal expense amount (${formatCurrency(totalAmount)})`)
      return
    }
  }

  addingExpense.value = true
  try {
    let splitType = 'equal'
    let splitDetails = null

    if (expenseForm.value.splitType === 'equal') {
      splitType = 'equal'
    } else if (expenseForm.value.splitType === 'one_person') {
      // One person owes all - create unequal split where only that person owes
      splitType = 'unequal'
      splitDetails = {}
      group.value.members.forEach(member => {
        if (member.id === expenseForm.value.owes_all_user_id) {
          splitDetails[member.id] = parseFloat(expenseForm.value.amount)
        } else {
          splitDetails[member.id] = 0
        }
      })
    } else if (expenseForm.value.splitType === 'custom') {
      splitType = 'unequal'
      splitDetails = expenseForm.value.customSplits
    }

    await axios.post(`/groups/${groupId}/expenses`, {
      amount: parseFloat(expenseForm.value.amount),
      description: expenseForm.value.description || expenseForm.value.category,
      expense_date: expenseForm.value.expense_date,
      paid_by: expenseForm.value.paid_by,
      split_type: splitType,
      split_details: splitDetails,
    })

    showAddExpenseModal.value = false
    // Reset form
    const initialSplits = {}
    if (group.value?.members) {
      group.value.members.forEach(member => {
        initialSplits[member.id] = 0
      })
    }
    expenseForm.value = {
      category: '',
      amount: '',
      description: '',
      expense_date: new Date().toISOString().slice(0, 10),
      paid_by: user.value?.id || null,
      splitType: 'equal',
      owes_all_user_id: null,
      customSplits: initialSplits,
    }
    loadGroupData()
  } catch (error) {
    console.error('Error adding expense:', error)
    alert('Failed to add expense')
  } finally {
    addingExpense.value = false
  }
}


const getBalanceClass = (amount) => {
  const absAmount = Math.abs(amount)
  if (absAmount >= 10000) { // 5 digits or more
    return 'text-sm'
  } else if (absAmount >= 1000) { // 4 digits
    return 'text-base'
  }
  return 'text-lg' // Default size
}

onMounted(async () => {
  if (groupId) {
    await loadGroupData()
    // Initialize paid_by to current user if available
    if (user.value && group.value?.members) {
      expenseForm.value.paid_by = user.value.id
      // Initialize custom splits for members if group data is available
      const initialSplits = {}
      group.value.members.forEach(member => {
        initialSplits[member.id] = 0
      })
      expenseForm.value.customSplits = initialSplits
    }
  }
})

// Watch for route changes to reload data
watch(() => route.params.id, async (newId) => {
  if (newId) {
    await loadGroupData()
    // Re-initialize form when group changes
    if (user.value && group.value?.members) {
      expenseForm.value.paid_by = user.value.id
      const initialSplits = {}
      group.value.members.forEach(member => {
        initialSplits[member.id] = 0
      })
      expenseForm.value.customSplits = initialSplits
    }
  }
}, { immediate: false })
</script>

