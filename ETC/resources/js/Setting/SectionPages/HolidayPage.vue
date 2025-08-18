<template>
  <form @submit="formSubmit">
    <div class="col-md-12 d-flex justify-content-between">
      <div class="d-flex">
        <CardTitle :title="$t('setting_sidebar.lbl_holiday')" icon="fa-solid fa-calendar-check" />
      </div>
    </div>

    <div class="row mb-2">
      <div class="col-12">
        <div class="form-group" v-if="branch.options.length > 1">
          <label class="form-label">{{ $t('branch.select_branch') }}<span class="text-danger">*</span></label>
          <Multiselect id="branch_id" v-model="branch_id" placeholder="Select Branch" v-bind="singleSelectOption" :options="branch.options" @select="branchSelect" class="form-group" />
          <span class="text-danger">{{ errors.branch_id }}</span>
        </div>
      </div>
      <div class="col-12">
        <div ref="calendarRef"></div>
      </div>
    </div>

    <SubmitButton :IS_SUBMITED="IS_SUBMITED" />
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import moment from 'moment'
import * as yup from 'yup'

import Calendar from '@event-calendar/core'
import DayGrid from '@event-calendar/day-grid'
import List from '@event-calendar/list'
import Interaction from '@event-calendar/interaction'

import { useField, useForm } from 'vee-validate'
import { useRequest } from '@/helpers/hooks/useCrudOpration'
import { useSelect } from '@/helpers/hooks/useSelect'

import CardTitle from '@/Setting/Components/CardTitle.vue'
import SubmitButton from './Forms/SubmitButton.vue'
import { LISTING_URL, STORE_URL } from '@/vue/constants/holiday'
import { BRANCH_LIST } from '@/vue/constants/branch'

const calendarRef = ref(null)
let calendarInstance = null

const events = ref([])
const IS_SUBMITED = ref(false)

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

const { getRequest, storeRequest } = useRequest()

const validationSchema = yup.object({
  branch_id: yup.string().required('Branch is required'),
})
const { handleSubmit, errors } = useForm({ validationSchema })
const { value: branch_id } = useField('branch_id')

const branch = ref({ options: [], list: [] })

const display_submit_message = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
  } else {
    window.errorSnackbar(res.message)
  }
}

const handleDateClick = (e) => {
  const clickedDate = moment(e.date).format('YYYY-MM-DD')
  const existingIndex = events.value.findIndex((event) => event.date === clickedDate)
  if (existingIndex >= 0) {
    events.value.splice(existingIndex, 1)
  } else {
    events.value.push({ title: 'Holiday', date: clickedDate })
  }
  calendarInstance.setOption('events', events.value)
}

onMounted(() => {
  useSelect({ url: BRANCH_LIST }, { value: 'id', label: 'name' }).then((data) => {
    branch.value = data
    if (data.options.length === 1) {
      branch_id.value = data.options[0].value
    }
  })

  // Instantiate calendar
  if (calendarRef.value) {
    calendarInstance = new Calendar(calendarRef.value, {
      plugins: [DayGrid, List, Interaction],
      view: 'dayGridMonth',
      dateClick: handleDateClick,
      events: events.value,
      headerToolbar: {
        start: 'prev,next today',
        center: 'title',
        end: 'dayGridMonth,listMonth'
      }
    })

    calendarInstance.render()
  }
})

const branchSelect = () => {
  events.value = []
  fetchData()
}

const fetchData = () => {
  getRequest({ url: LISTING_URL, id: { branch_id: branch_id.value } }).then((res) => {
    if (res.status) {
      events.value = res.data.map(item => ({
        title: item.title,
        date: moment(item.date).format('YYYY-MM-DD')
      }))
      calendarInstance.setOption('events', events.value)
    }
  })
}

const formSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true
  values.holidays = events.value
  storeRequest({ url: STORE_URL, body: values }).then((res) => display_submit_message(res))
})
</script>

<style>
@import '@event-calendar/core/index.css';

.fc-h-event {
  --fc-event-bg-color: var(--bs-primary);
  --fc-event-border-color: var(--bs-primary);
}

.dark .ec-day {
  background-color: var(--bs-black);
}
</style>
