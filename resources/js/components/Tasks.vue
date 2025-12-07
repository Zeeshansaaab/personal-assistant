<template>
  <div class="w-full pb-6 px-4 sm:px-6 bg-transparent">
    <Header title="Tasks & Reminders" subtitle="Manage your tasks and reminders">
      <template #actions>
        <GlassButton @click="openModal" variant="primary">
          + Add Task
        </GlassButton>
      </template>
    </Header>

    <div v-if="tasks.length === 0" class="text-center py-16">
      <GlassCard class="max-w-md mx-auto">
        <p class="dark:text-white/70 text-slate-600 mb-6">No tasks yet. Create your first task!</p>
        <GlassButton @click="openModal" variant="primary">
          Add Task
        </GlassButton>
      </GlassCard>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <GlassCard
        v-for="task in tasks"
        :key="task.id"
        hoverable
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3
              class="text-xl font-bold dark:text-white text-slate-900 mb-2"
              :class="{ 'line-through opacity-60': task.is_completed }"
            >
              {{ task.title }}
            </h3>
            <p
              v-if="task.description"
              class="dark:text-white/60 text-slate-600 text-sm mb-3"
            >
              {{ task.description }}
            </p>
          </div>
          <span
            :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              task.is_completed
                ? 'bg-green-500/20 text-green-600 dark:text-green-300'
                : 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-300',
            ]"
          >
            {{ task.is_completed ? 'Done' : 'Pending' }}
          </span>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex items-center gap-2 text-sm dark:text-white/60 text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(task.due_date) }}</span>
          </div>
          <div v-if="task.repeat_type !== 'none'" class="flex items-center gap-2">
            <span
              class="px-2 py-1 rounded-lg text-xs dark:bg-white/10 bg-slate-100 dark:text-white/70 text-slate-600"
            >
              {{ task.repeat_type }}
            </span>
          </div>
          <div v-if="task.daily_reminder || task.weekend_reminder" class="flex gap-2 flex-wrap">
            <span
              v-if="task.daily_reminder"
              class="px-2 py-1 rounded-lg text-xs bg-blue-500/20 text-blue-600 dark:text-blue-300"
            >
              Daily
            </span>
            <span
              v-if="task.weekend_reminder"
              class="px-2 py-1 rounded-lg text-xs bg-purple-500/20 text-purple-600 dark:text-purple-300"
            >
              Weekend
            </span>
          </div>
        </div>

        <div class="flex gap-2 pt-4 border-t dark:border-white/10 border-slate-200">
          <GlassButton
            @click.stop="toggleComplete(task)"
            variant="secondary"
            class="flex-1 text-xs"
          >
            {{ task.is_completed ? 'Mark Pending' : 'Mark Done' }}
          </GlassButton>
          <GlassButton
            @click.stop="editTask(task)"
            variant="outline"
            class="flex-1 text-xs"
          >
            Edit
          </GlassButton>
          <GlassButton
            @click.stop="deleteTask(task.id)"
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
          {{ editingTask ? 'Edit Task' : 'Add New Task' }}
        </h3>
        <form @submit.prevent="saveTask" class="space-y-4">
          <GlassInput
            v-model="form.title"
            label="Title *"
            placeholder="Enter task title"
            required
          />
          <GlassInput
            v-model="form.description"
            label="Description"
            placeholder="Optional description"
            type="textarea"
          />
          <GlassInput
            v-model="form.due_date"
            label="Due Date"
            type="date"
          />
          <div>
            <label class="block text-sm font-medium dark:text-white/90 text-slate-700 mb-2">
              Repeat Type
            </label>
            <select
              v-model="form.repeat_type"
              class="w-full px-4 py-3 rounded-xl dark:bg-white/10 bg-slate-100 dark:text-white text-slate-900 border dark:border-white/20 border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="none">None</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div class="flex items-center gap-3">
            <input
              v-model="form.daily_reminder"
              type="checkbox"
              id="daily_reminder"
              class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label for="daily_reminder" class="dark:text-white/90 text-slate-700">
              Daily Reminder
            </label>
          </div>
          <div class="flex items-center gap-3">
            <input
              v-model="form.weekend_reminder"
              type="checkbox"
              id="weekend_reminder"
              class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label for="weekend_reminder" class="dark:text-white/90 text-slate-700">
              Weekend Reminder
            </label>
          </div>
          <div class="flex gap-4 pt-4">
            <GlassButton type="button" @click="closeModal" variant="secondary" class="flex-1">
              Cancel
            </GlassButton>
            <GlassButton type="submit" variant="primary" class="flex-1">
              Save Task
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

const tasks = ref([])
const showModal = ref(false)
const editingTask = ref(null)
const form = ref({
  title: '',
  description: '',
  due_date: '',
  repeat_type: 'none',
  daily_reminder: false,
  weekend_reminder: false,
})

const fetchTasks = async () => {
  try {
    const response = await axios.get('/tasks')
    tasks.value = response.data
  } catch (error) {
    console.error('Error fetching tasks:', error)
    alert('Failed to fetch tasks')
  }
}

const openModal = () => {
  editingTask.value = null
  resetForm()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingTask.value = null
  resetForm()
}

const resetForm = () => {
  form.value = {
    title: '',
    description: '',
    due_date: '',
    repeat_type: 'none',
    daily_reminder: false,
    weekend_reminder: false,
  }
}

const editTask = (task) => {
  editingTask.value = task
  // Format date for date input (YYYY-MM-DD)
  const formatDateForInput = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toISOString().split('T')[0]
  }
  form.value = {
    title: task.title,
    description: task.description || '',
    due_date: formatDateForInput(task.due_date),
    repeat_type: task.repeat_type || 'none',
    daily_reminder: task.daily_reminder || false,
    weekend_reminder: task.weekend_reminder || false,
  }
  showModal.value = true
}

const saveTask = async () => {
  try {
    if (editingTask.value) {
      await axios.put(`/tasks/${editingTask.value.id}`, form.value)
    } else {
      await axios.post('/tasks', form.value)
    }
    closeModal()
    fetchTasks()
  } catch (error) {
    console.error('Error saving task:', error)
    alert('Failed to save task')
  }
}

const toggleComplete = async (task) => {
  try {
    await axios.put(`/tasks/${task.id}`, {
      is_completed: !task.is_completed
    })
    fetchTasks()
  } catch (error) {
    console.error('Error updating task:', error)
    alert('Failed to update task')
  }
}

const deleteTask = async (id) => {
  if (!confirm('Are you sure you want to delete this task?')) return
  try {
    await axios.delete(`/tasks/${id}`)
    fetchTasks()
  } catch (error) {
    console.error('Error deleting task:', error)
    alert('Failed to delete task')
  }
}

const formatDate = (date) => {
  if (!date) return 'No due date'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchTasks()
})
</script>
