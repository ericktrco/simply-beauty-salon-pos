<template>
  <div>
    <h4 class="mb-3">Walk-in POS - {{ branch?.name }}</h4>
    <div class="mb-3">
      <span class="badge bg-primary">Branch #{{ branch?.id }}</span>
    </div>

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Customer</label>
        <Multiselect id="user_id" v-model="user_id" placeholder="Select Customer" :options="customerOptions" track-by="value" label="label" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Staff</label>
        <Multiselect id="employee_id" v-model="employee_id" placeholder="Select Staff" :options="employeeOptions" track-by="value" label="label" @change="onEmployeeChange" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Services</label>
        <Multiselect id="services_id" v-model="services_id" placeholder="Select Services" :options="serviceOptions" :multiple="true" track-by="value" label="label" />
      </div>
    </div>

    <div class="mt-4 d-flex align-items-center justify-content-between">
      <div><strong>Total:</strong> {{ formatCurrency(totalAmount) }}</div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-secondary" @click="resetForm">Reset</button>
        <button type="button" class="btn btn-primary" :disabled="!canSubmit" @click="createWalkInBooking">Create Sale</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { createRequest } from '@/helpers/utilities'
import { CUSTOMER_LIST, EMPLOYEE_LIST, SERVICE_LIST, STORE_URL } from '../constant/booking'

const props = defineProps({
  initialBranch: { type: Object, required: true }
})

const branch = ref(null)
const user_id = ref(null)
const employee_id = ref(null)
const services_id = ref([])
const customerOptions = ref([])
const employeeOptions = ref([])
const serviceOptions = ref([])

const totalAmount = computed(() => {
  // Sum price from selected services where option has "price" attached (if provided)
  const map = new Map(serviceOptions.value.map((o) => [o.value, o]))
  return services_id.value.reduce((sum, sid) => sum + (map.get(sid)?.price || 0), 0)
})

const canSubmit = computed(() => !!(branch.value?.id && user_id.value && employee_id.value && services_id.value.length))

const formatCurrency = (amount) => {
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' }).format(amount)
  } catch {
    return amount
  }
}

const loadCustomers = async () => {
  const res = await createRequest(CUSTOMER_LIST())
  if (res?.status !== false) {
    customerOptions.value = (res?.data || res || []).map((u) => ({ value: u.id, label: u.full_name || u.name || u.email }))
  }
}

const loadEmployees = async () => {
  const res = await createRequest(EMPLOYEE_LIST({ branch_id: branch.value.id }))
  if (res?.status !== false) {
    employeeOptions.value = (res?.data || res || []).map((e) => ({ value: e.id, label: e.name || e.full_name }))
  }
}

const loadServices = async () => {
  serviceOptions.value = []
  services_id.value = []
  if (!employee_id.value) return
  const res = await createRequest(SERVICE_LIST({ id: employee_id.value, branch_id: branch.value.id }))
  const list = Array.isArray(res) ? res : res?.data || []
  serviceOptions.value = list.map((s) => ({ value: s.service_id, label: s.service_name, price: Number(s.service_price) || 0, duration_min: Number(s.duration_min) || 0 }))
}

const onEmployeeChange = () => {
  loadServices()
}

const resetForm = () => {
  user_id.value = null
  employee_id.value = null
  services_id.value = []
  serviceOptions.value = []
}

const createWalkInBooking = async () => {
  const now = new Date()
  const payload = {
    branch_id: branch.value.id,
    user_id: user_id.value,
    status: 'confirmed',
    start_date_time: now.toISOString().slice(0, 19).replace('T', ' '),
    services: services_id.value.map((sid) => {
      const meta = serviceOptions.value.find((s) => s.value === sid)
      return {
        employee_id: employee_id.value,
        service_id: sid,
        service_price: meta?.price || 0,
        duration_min: meta?.duration_min || 0,
        start_date_time: now.toISOString().slice(0, 19).replace('T', ' ')
      }
    })
  }

  const res = await createRequest(STORE_URL(), {}, payload)
  if (res?.status) {
    window.successSnackbar?.(res.message || 'Created')
    resetForm()
  } else {
    window.errorSnackbar?.(res?.message || 'Failed to create')
  }
}

onMounted(async () => {
  branch.value = props.initialBranch
  await Promise.all([loadCustomers(), loadEmployees()])
})
</script>
