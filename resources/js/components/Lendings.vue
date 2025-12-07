<template>
  <div class="w-full pb-6 px-4 sm:px-6 bg-transparent">
    <Header title="Money Lending & Items" subtitle="Track money and items you've lent">
      <template #actions>
        <GlassButton @click="openModal" variant="primary">
          + Add Entry
        </GlassButton>
      </template>
    </Header>

    <div v-if="lendings.length === 0" class="text-center py-16">
      <GlassCard class="max-w-md mx-auto">
        <p class="dark:text-white/70 text-slate-600 mb-6">No lending records yet. Add your first entry!</p>
        <GlassButton @click="openModal" variant="primary">
          Add Entry
        </GlassButton>
      </GlassCard>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <GlassCard
        v-for="lending in lendings"
        :key="lending.id"
        hoverable
        :class="{ 'border-l-4 border-red-500': isOverdue(lending) && !lending.is_returned }"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-2">
              {{ lending.person_name }}
            </h3>
            <div class="flex items-center gap-2 mb-2">
              <span
                :class="[
                  'px-2 py-1 rounded-lg text-xs',
                  lending.item_type === 'money'
                    ? 'bg-green-500/20 text-green-600 dark:text-green-300'
                    : 'bg-blue-500/20 text-blue-600 dark:text-blue-300',
                ]"
              >
                {{ lending.item_type === 'money' ? 'Money' : 'Item' }}
              </span>
              <span class="text-lg font-bold dark:text-white text-slate-900">
                {{
                  lending.item_type === 'money'
                    ? formatCurrency(lending.amount)
                    : lending.item_description
                }}
              </span>
            </div>
          </div>
          <span
            :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              lending.is_returned
                ? 'bg-green-500/20 text-green-600 dark:text-green-300'
                : isOverdue(lending)
                ? 'bg-red-500/20 text-red-600 dark:text-red-300'
                : 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-300',
            ]"
          >
            {{ lending.is_returned ? 'Returned' : isOverdue(lending) ? 'Overdue' : 'Pending' }}
          </span>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex items-center gap-2 text-sm dark:text-white/60 text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Given: {{ formatDate(lending.date_given) }}</span>
          </div>
          <div class="flex items-center gap-2 text-sm dark:text-white/60 text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Expected: {{ formatDate(lending.expected_return_date) }}</span>
          </div>
          <p v-if="lending.notes" class="text-sm italic dark:text-white/60 text-slate-600 mt-2">
            {{ lending.notes }}
          </p>
        </div>

        <div class="flex gap-2 pt-4 border-t dark:border-white/10 border-slate-200">
          <GlassButton
            v-if="!lending.is_returned"
            @click.stop="markReturned(lending.id)"
            variant="primary"
            class="flex-1 text-xs"
          >
            Mark Returned
          </GlassButton>
          <GlassButton
            @click.stop="editLending(lending)"
            variant="outline"
            class="flex-1 text-xs"
          >
            Edit
          </GlassButton>
          <GlassButton
            @click.stop="deleteLending(lending.id)"
            variant="outline"
            class="flex-1 text-xs"
            style="border-color: rgb(239, 68, 68); color: rgb(239, 68, 68);"
          >
            Delete
          </GlassButton>
        </div>
      </GlassCard>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <GlassCard class="max-w-md w-full p-4 sm:p-6 lg:p-8 mx-4">
        <h3 class="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-4 sm:mb-6">
          {{ editingLending ? 'Edit Entry' : 'Add New Entry' }}
        </h3>
        <form @submit.prevent="saveLending" class="space-y-4">
          <GlassInput
            v-model="form.person_name"
            label="Person Name *"
            placeholder="Enter person's name"
            required
          />
          <div>
            <label class="block text-sm font-medium dark:text-white/90 text-slate-700 mb-2">
              Type *
            </label>
            <select
              v-model="form.item_type"
              @change="onTypeChange"
              class="w-full px-4 py-3 rounded-xl dark:bg-white/10 bg-slate-100 dark:text-white text-slate-900 border dark:border-white/20 border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            >
              <option value="money">Money</option>
              <option value="item">Item</option>
            </select>
          </div>
          <GlassInput
            v-if="form.item_type === 'money'"
            v-model="form.amount"
            label="Amount *"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            required
          />
          <GlassInput
            v-else
            v-model="form.item_description"
            label="Item Description *"
            placeholder="Describe the item"
            required
          />
          <GlassInput
            v-model="form.date_given"
            label="Date Given *"
            type="date"
            required
          />
          <GlassInput
            v-model="form.expected_return_date"
            label="Expected Return Date *"
            type="date"
            required
          />
          <GlassInput
            v-model="form.notes"
            label="Notes"
            placeholder="Optional notes"
            type="textarea"
          />
          <div class="flex gap-4 pt-4">
            <GlassButton type="button" @click="closeModal" variant="secondary" class="flex-1">
              Cancel
            </GlassButton>
            <GlassButton type="submit" variant="primary" class="flex-1">
              Save Entry
            </GlassButton>
          </div>
        </form>
      </GlassCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Header from './layout/Header.vue'
import GlassCard from './common/GlassCard.vue'
import GlassInput from './common/GlassInput.vue'
import GlassButton from './common/GlassButton.vue'
import { useCurrency } from '../composables/useCurrency'

const { formatCurrency } = useCurrency()

const lendings = ref([])
const showModal = ref(false)
const editingLending = ref(null)
const form = ref({
  person_name: '',
  item_type: 'money',
  amount: '',
  item_description: '',
  date_given: '',
  expected_return_date: '',
  notes: '',
})

const fetchLendings = async () => {
  try {
    const response = await axios.get('/lendings')
    lendings.value = response.data
  } catch (error) {
    console.error('Error fetching lendings:', error)
    alert('Failed to fetch lending records')
  }
}

const openModal = () => {
  editingLending.value = null
  resetForm()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingLending.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    person_name: '',
    item_type: 'money',
    amount: '',
    item_description: '',
    date_given: '',
    expected_return_date: '',
    notes: '',
  }
}

const onTypeChange = () => {
  if (form.value.item_type === 'money') {
    form.value.item_description = ''
  } else {
    form.value.amount = ''
  }
}

const editLending = (lending) => {
  editingLending.value = lending
  // Format date for date input (YYYY-MM-DD)
  const formatDateForInput = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toISOString().split('T')[0]
  }
  form.value = {
    person_name: lending.person_name,
    item_type: lending.item_type,
    amount: lending.amount || '',
    item_description: lending.item_description || '',
    date_given: formatDateForInput(lending.date_given),
    expected_return_date: formatDateForInput(lending.expected_return_date),
    notes: lending.notes || '',
  }
  showModal.value = true
}

const saveLending = async () => {
  try {
    if (editingLending.value) {
      await axios.put(`/lendings/${editingLending.value.id}`, form.value)
    } else {
      await axios.post('/lendings', form.value)
    }
    closeModal()
    fetchLendings()
  } catch (error) {
    console.error('Error saving lending:', error)
    alert('Failed to save entry')
  }
}

const markReturned = async (id) => {
  try {
    await axios.post(`/lendings/${id}/mark-returned`)
    fetchLendings()
  } catch (error) {
    console.error('Error marking as returned:', error)
    alert('Failed to update entry')
  }
}

const deleteLending = async (id) => {
  if (!confirm('Are you sure you want to delete this entry?')) return
  try {
    await axios.delete(`/lendings/${id}`)
    fetchLendings()
  } catch (error) {
    console.error('Error deleting lending:', error)
    alert('Failed to delete entry')
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

const isOverdue = (lending) => {
  if (lending.is_returned) return false
  const today = new Date()
  const returnDate = new Date(lending.expected_return_date)
  return returnDate < today
}

onMounted(() => {
  fetchLendings()
})
</script>
