<template>
  <div class="pagination-controls">
    <nav aria-label="Pagination">
      <ul class="pagination justify-content-end">
        <!-- Previous Button -->
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button @click="prevPage" class="page-link" :disabled="currentPage === 1">Previous</button>
        </li>
        <!-- Page Number Before Current -->
        <li v-if="currentPage > 1" class="page-item">
          <button @click="goToPage(currentPage - 1)" class="page-link">{{ currentPage - 1 }}</button>
        </li>
        <!-- Current Page -->
        <li class="page-item active" aria-current="page">
          <span class="page-link">{{ currentPage }}</span>
        </li>
        <!-- Page Number After Current -->
        <li v-if="currentPage < totalPages" class="page-item">
          <button @click="goToPage(currentPage + 1)" class="page-link">{{ currentPage + 1 }}</button>
        </li>
        <!-- Next Button -->
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button @click="nextPage" class="page-link" :disabled="currentPage === totalPages">Next</button>
        </li>
      </ul>
    </nav>
  </div>
  <div ref="calenderRef"></div>
  <booking-form :booking-type="bookingType" :status-list="bookingStatus" @onSubmit="onSubmitEvent" :booking-data="bookingData"></booking-form>
</template>
<script setup>
import { reactive, ref, computed, onMounted, onUnmounted } from 'vue'
import { createRequest } from '@/helpers/utilities'

import Calendar from '@event-calendar/core'
import DayGrid from '@event-calendar/day-grid'
import List from '@event-calendar/list'
import TimeGrid from '@event-calendar/time-grid'
import ResourceTimeGrid from '@event-calendar/resource-time-grid'
import Interaction from '@event-calendar/interaction'

import BookingForm from './BookingForm.vue'
import { INDEX_URL } from '../constant/booking'
// import * as moment from 'moment'

const currentPage = ref(1)
const perPage = ref(6)
const totalEmployees = ref(0)
const totalPages = computed(() => {
  const pages = Math.ceil(totalEmployees.value / perPage.value)
  return pages > 0 ? pages : 1
})

const props = defineProps({
  status: { type: [String, Object], required: true },
  slotDuration: { type: String },
  branchId: { type: [String, Number] },
  date: { type: Date, default: () => new Date() }
})

const slotsDurations = props.slotDuration || '00:15'
const bookingStatus = ref(typeof props.status === 'string' ? JSON.parse(props.status) : props.status)
const calenderRef = ref(null)
const calenderInit = ref(null)
const bookingType = ref('')
const bookingData = reactive({
  id: 0,
  start_date_time: null,
  employee_id: null,
  branch_id: props.branchId
})

// --- Booking Form ---
const setBooking = (info) => {
  bookingData.id = info.id || 0
  bookingData.employee_id = info?.resource?.id || info?.resourceIds?.[0] || null
  bookingData.start_date_time = info.date || null
}

const showBookingForm = (info) => {
  bookingType.value = 'CALENDER_BOOKING'
  const elem = document.getElementById('booking-form')
  setBooking(info)
  const form = bootstrap.Offcanvas.getOrCreateInstance(elem)
  form.show()
  document.querySelector('.offcanvas-backdrop')?.remove()
  document.body.classList.add('calender-view')
}

const hideBookingForm = () => {
  const elem = document.getElementById('booking-form')
  const form = bootstrap.Offcanvas.getOrCreateInstance(elem)
  form.hide()
  document.body.classList.remove('calender-view')
}

const createBooking = () => {
  bookingType.value = 'CREATE_BOOKING'
  showBookingForm({})
}

onMounted(() => {
  // Add event listener for drawer close
  const elem = document.getElementById('booking-form')
  if (elem) {
    elem.addEventListener('hide.bs.offcanvas', () => {
      setBooking({})
      bookingType.value = ''
      document.body.classList.remove('calender-view')
    })
  }
})

onUnmounted(() => {
  const elem = document.getElementById('booking-form')
  if (elem) {
    elem.removeEventListener('hide.bs.offcanvas', () => {
      setBooking({})
      bookingType.value = ''
      document.body.classList.remove('calender-view')
    })
  }
})

// --- Fetch & Render Events ---
const fetchAndRenderEvents = async () => {
  const params = { page: currentPage.value, per_page: perPage.value }
  const res = await createRequest(INDEX_URL(params))
  const { employees, data } = res
  totalEmployees.value = res.total_count

  // Set resources
  calenderInit.value.setOption(
    'resources',
    employees.map((emp) => ({ id: emp.id, title: emp.title }))
  )

  // Set events
  calenderInit.value.setOption(
    'events',
    data.map((event) => ({
      id: event.id,
      title: event.title,
      start: event.start,
      end: event.end,
      resourceIds: [event.resourceId], // Important for ResourceTimeGrid
      backgroundColor: event.color || '#3788d8', // Use status color from backend
      borderColor: event.color || '#3788d8'
    }))
  )
}

// --- Calendar Initialization ---
onMounted(() => {
  calenderInit.value = new Calendar({
    target: calenderRef.value,
    props: {
      plugins: [DayGrid, List, TimeGrid, ResourceTimeGrid, Interaction],
      options: {
        date: props.date,
        view: 'resourceTimeGridDay',
        height: '800px',
        slotEventOverlap: false,
        dragScroll: false,
        scrollTime: '09:00:00',
        slotDuration: slotsDurations,
        dayMaxEvents: true,
        nowIndicator: true,
        selectable: false,
        headerToolbar: {
          start: 'prev,next today',
          center: 'title',
          end: 'resourceTimeGridDay'
        },
        buttonText: (texts) => {
          texts.resourceTimeGridDay = 'Day'
          texts.resourceTimeGridWeek = 'Week'
          return texts
        },
        eventContent: (data) => {
          return { html: data.event.title + ' ' + data.timeText }
        },
        dateClick: showBookingForm,
        select: showBookingForm,
        eventClick: (info) => {
          showBookingForm({
            id: info.event.id,
            resourceIds: info.event.resourceIds,
            date: info.event.start
          })
        }
      }
    }
  })

  // Initial fetch
  fetchAndRenderEvents()

  // If booking_id is in URL
  const bkid = new URL(location.href).searchParams.get('booking_id')
  if (bkid) showBookingForm({ id: bkid })
})

// --- Pagination ---
const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchAndRenderEvents()
  }
}
const nextPage = () => {
  if (currentPage.value * perPage.value < totalEmployees.value) {
    currentPage.value++
    fetchAndRenderEvents()
  }
}
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchAndRenderEvents()
  }
}

// --- After Booking Submit ---
const onSubmitEvent = () => {
  setTimeout(() => fetchAndRenderEvents(), 500)
}
</script>

<style>
@import '@event-calendar/core/index.css';
body {
  transition: width 400ms ease;
}
.calender-view {
  width: calc(100% - 382px);
  transition: width 400ms ease;
}
.ec-lines {
  width: unset;
  margin-left: 8px;
}
.ec-header .ec-day {
  overflow: inherit !important;
  height: inherit !important;
  line-height: inherit;
  min-height: inherit;
}
.ec-day.ec-today {
  background-color: var(--bs-body-bg);
}
.dark .ec-day.ec-today {
  background-color: #181818;
}
.ec-event {
  border-radius: 0;
  border-bottom: 2px solid var(--bs-border-color);
  cursor: pointer;
}
.ec-body:not(.ec-compact) .ec-line:nth-child(even):after {
  border-bottom-style: solid;
}
.ec-line:not(:first-child):after {
  border-color: var(--bs-border-color);
}
.ec-header,
.ec-all-day,
.ec-body,
.ec-days,
.ec-day {
  border-color: var(--bs-border-color);
}
.ec-button,
.ec-button:not(:disabled) {
  color: var(--bs-body-color);
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
}
.dark .ec-button:not(:disabled):hover,
.dark .ec-button.ec-active {
  border-color: var(--bs-border-color);
  background-color: var(--bs-body-bg);
}
.ec-icon.ec-prev:after,
.ec-icon.ec-next:after {
  border-color: var(--bs-body-color);
}
</style>
