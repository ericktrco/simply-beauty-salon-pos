import { InitApp } from '@/helpers/main'
import BillingPage from './components/BillingPage.vue'

const app = InitApp()
app.component('billing-page', BillingPage)
app.mount('[data-render="app"]')
