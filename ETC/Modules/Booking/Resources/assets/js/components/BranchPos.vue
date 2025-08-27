<template>
  <div>
    <h4 class="mb-3">POS - {{ branch?.name }}</h4>
    <div class="mb-3">
      <span class="badge bg-primary">Branch #{{ branch?.id }}</span>
    </div>
    <booking-form :hide-branch-and-time="true" :booking-type="'CREATE_BOOKING'" :status-list="{}" @onSubmit="noop" :booking-data="{ branch_id: branch?.id }" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import BookingForm from './BookingForm.vue'

const props = defineProps({
  initialBranch: { type: Object, required: true }
})

const branch = ref(null)
const noop = () => {}

onMounted(() => {
  branch.value = props.initialBranch
})
</script>
