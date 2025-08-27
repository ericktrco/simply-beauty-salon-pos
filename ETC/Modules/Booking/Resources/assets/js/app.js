// import "./calender"
import { InitApp } from '@/helpers/main'
import { createPinia } from 'pinia'

import CalendarView from './components/CalendarView.vue'
import BookingForm from './components/BookingForm.vue'
import BranchPos from './components/BranchPos.vue'
import WalkInPos from './components/WalkInPos.vue'

const pinia = createPinia()
const app = InitApp()

app.component('booking-form', BookingForm)
app.component('calendar-view', CalendarView)
app.component('walk-in-pos', WalkInPos)
app.use(pinia)
app.mount('[data-render="app"]')
