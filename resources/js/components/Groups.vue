<template>
  <div class="w-full pb-6 px-4 sm:px-6">
    <Header title="Groups" subtitle="Split expenses with friends">
      <template #actions>
        <GlassButton @click="showCreateModal = true" variant="primary">
          + Create Group
        </GlassButton>
      </template>
    </Header>

    <div v-if="groups.length === 0" class="text-center py-16">
      <GlassCard class="max-w-md mx-auto">
        <p class="text-white/70 mb-6">No groups yet. Create your first group to start splitting expenses!</p>
        <GlassButton @click="showCreateModal = true" variant="primary">
          Create Group
        </GlassButton>
      </GlassCard>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <GlassCard
        v-for="group in groups"
        :key="group.id"
        hoverable
        class="cursor-pointer"
        @click="viewGroup(group.id)"
      >
        <h3 class="text-xl font-bold dark:text-white text-slate-900 mb-2">{{ group.name }}</h3>
        <p v-if="group.description" class="dark:text-white/60 text-slate-600 text-sm mb-4">{{ group.description }}</p>
        <div class="flex items-center justify-between">
          <span class="dark:text-white/70 text-slate-600 text-sm">{{ group.members?.length || 0 }} members</span>
          <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300">
            Active
          </span>
        </div>
      </GlassCard>
    </div>

    <!-- Create Group Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showCreateModal = false">
      <GlassCard class="max-w-md w-full p-4 sm:p-6 lg:p-8 mx-4">
        <h3 class="text-xl sm:text-2xl font-bold dark:text-white text-slate-900 mb-4 sm:mb-6">Create New Group</h3>
        <form @submit.prevent="createGroup" class="space-y-4">
          <GlassInput
            v-model="newGroup.name"
            label="Group Name"
            placeholder="e.g., Dinner Group"
            required
          />
          <GlassInput
            v-model="newGroup.description"
            label="Description"
            placeholder="Optional description"
          />
          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Add Members</label>
            <div class="flex gap-2 mb-2">
              <GlassInput
                v-model="searchQuery"
                placeholder="Search by email, mobile, or name"
                @input="searchUsers"
                class="flex-1"
              />
            </div>
            <div v-if="searchResults.length > 0" class="glass-strong rounded-2xl p-2 max-h-40 overflow-y-auto">
              <div
                v-for="user in searchResults"
                :key="user.id"
                class="p-2 rounded-xl hover:bg-white/10 cursor-pointer transition-colors"
                @click="addMember(user)"
              >
                <p class="text-white font-medium">{{ user.name }}</p>
                <p class="text-white/60 text-sm">{{ user.email || user.mobile }}</p>
              </div>
            </div>
            <div v-if="selectedMembers.length > 0" class="mt-4">
              <p class="text-sm text-white/70 mb-2">Selected Members:</p>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="member in selectedMembers"
                  :key="member.id"
                  class="px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-sm flex items-center gap-2"
                >
                  {{ member.name }}
                  <button type="button" @click="removeMember(member.id)" class="hover:text-white">×</button>
                </span>
              </div>
            </div>
          </div>
          <div class="flex gap-4">
            <GlassButton type="button" @click="showCreateModal = false" variant="secondary" class="flex-1">
              Cancel
            </GlassButton>
            <GlassButton type="submit" :loading="creating" variant="primary" class="flex-1">
              Create Group
            </GlassButton>
          </div>
        </form>
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
import GlassInput from './common/GlassInput.vue'
import GlassButton from './common/GlassButton.vue'

const router = useRouter()

const groups = ref([])
const showCreateModal = ref(false)
const creating = ref(false)
const newGroup = ref({
  name: '',
  description: '',
})
const searchQuery = ref('')
const searchResults = ref([])
const selectedMembers = ref([])

const loadGroups = async () => {
  try {
    const response = await axios.get('/groups')
    groups.value = response.data
  } catch (error) {
    console.error('Error loading groups:', error)
  }
}

const searchUsers = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }
  try {
    const response = await axios.get('/users/search', {
      params: { q: searchQuery.value }
    })
    searchResults.value = response.data
  } catch (error) {
    console.error('Error searching users:', error)
  }
}

const addMember = (user) => {
  if (!selectedMembers.value.find(m => m.id === user.id)) {
    selectedMembers.value.push(user)
  }
  searchQuery.value = ''
  searchResults.value = []
}

const removeMember = (userId) => {
  selectedMembers.value = selectedMembers.value.filter(m => m.id !== userId)
}

const createGroup = async () => {
  creating.value = true
  try {
    const response = await axios.post('/groups', {
      name: newGroup.value.name,
      description: newGroup.value.description,
      member_ids: selectedMembers.value.map(m => m.id)
    })
    groups.value.push(response.data)
    showCreateModal.value = false
    newGroup.value = { name: '', description: '' }
    selectedMembers.value = []
  } catch (error) {
    console.error('Error creating group:', error)
    alert('Failed to create group')
  } finally {
    creating.value = false
  }
}

const viewGroup = (groupId) => {
  router.push(`/groups/${groupId}`)
}

onMounted(() => {
  loadGroups()
})
</script>

