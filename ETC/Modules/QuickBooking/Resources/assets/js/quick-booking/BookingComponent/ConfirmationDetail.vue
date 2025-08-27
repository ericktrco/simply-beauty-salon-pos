<template>
  <div class="card-list-data" v-if="booking">
    <div class="row">
      <div class="col-sm-6">
        <div class="confirmation-info-section mb-5">
          <h6 class="text-primary text-uppercase fw-bold mb-3">{{ $t('quick_booking.lbl_salon_info') }}</h6>
          <div class="iq-card bg-soft-primary text-body p-3">
            <div class="iq-card-body">
              <table class="iq-table-border mb-0" style="border: 0">
                <tbody>
                  <tr>
                    <td>
                      <h6 style="width: 15em" class="mb-2">{{ booking.branch.name }}</h6>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <p style="width: 15em" class="mb-2">{{ booking.branch_address.country }} / {{ booking.branch_address.postal_code }}</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="confirmation-info-section">
          <h6 class="text-primary text-uppercase fw-bold mb-3">{{ $t('quick_booking.lbl_customer_info') }}</h6>
          <div class="iq-card bg-soft-primary text-body p-3">
            <div class="iq-card-body">
              <table class="iq-table-border mb-0" style="border: 0">
                <tbody>
                  <tr>
                    <td>
                      <h6 class="mb-2">{{ $t('quick_booking.lbl_name') }}:</h6>
                    </td>
                    <td>
                      <p class="mb-2">{{ booking.user.full_name }}</p>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <h6 class="mb-2">{{ $t('quick_booking.lbl_number') }}:</h6>
                    </td>
                    <td>
                      <p class="mb-2">{{ booking.user.mobile }}</p>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <h6 class="mb-2">{{ $t('quick_booking.lbl_mail') }}:</h6>
                    </td>
                    <td>
                      <p class="mb-2">{{ booking.user.email }}</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <h6 class="text-primary text-uppercase fw-bold mb-3">{{ $t('quick_booking.lbl_summary') }}</h6>
        <div class="iq-card iq-card-border p-3">
          <div class="d-flex justify-content-between align-items-center">
            <p class="m-0">{{ $t('quick_booking.lbl_staff') }} :</p>
            <h6 class="m-0">{{ booking.services[0].employee.full_name }}</h6>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="m-0">{{ $t('quick_booking.lbl_date') }} :</p>
            <h6>
              <span id="dateOfAppointment">{{ booking.start_date_time }}</span>
            </h6>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="m-0">{{ $t('quick_booking.lbl_time') }} :</p>
            <h6 class="m-0">
              <span>{{ booking.services[0].start_date_time }}</span>
            </h6>
          </div>
          <div class="iq-card bg-soft-primary text-body p-3 mt-4 mb-0 shadow-none">
            <div class="iq-card-body">
              <h6>{{ $t('quick_booking.lbl_service') }}</h6>
              <div class="services_list">
                <div class="d-flex justify-content-between align-items-center mt-2" v-for="service in booking.services" :key="service">
                  <p class="m-0">{{ service.service_name }}</p>
                  <h6 class="m-0">{{ formatCurrencyVue(service.service_price) }}/-</h6>
                </div>
              </div>
            </div>
            <div class="iq-card-body">
              <h6>Taxes</h6>
              <template v-if="booking.tax">
                <div class="d-flex align-items-center justify-content-between" v-for="(tax, index) in booking.tax" :key="index">
                  <template v-if="tax.type == 'percent'">
                    <p>{{ tax.name }}: {{ tax.percent + '%' }}</p>
                    <h6>+ {{ currency_symbol }} {{ calculatePercentAmount(tax.percent) }}</h6>
                  </template>
                  <template v-else>
                    <p>{{ tax.name }}:</p>
                    <h6>+ {{ formatCurrencyVue(tax.tax_amount) }}</h6>
                  </template>
                </div>
              </template>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
              <h5>{{ $t('quick_booking.lbl_price') }}</h5>
              <h5 class="text-primary services-total">{{ formatCurrencyVue(finalAmount) }}/-</h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card-footer non-printable">
    <div class="d-flex flex-wrap gap-1 justify-content-center">
      <button type="button" class="btn btn-primary d-flex gap-3" @click="reset">
        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>{{ $t('quick_booking.lbl_more_appointment') }}</span>
      </button>
      <button type="button" class="btn btn-secondary d-flex gap-3" @click="print">
        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
        </svg>
        <span>{{ $t('quick_booking.lbl_pdf') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue'
import { useQuickBooking } from '../../store/quick-booking'
const props = defineProps({
  wizardNext: {
    default: '',
    type: [String, Number]
  },
  wizardPrev: {
    default: '',
    type: [String, Number]
  }
})
const emit = defineEmits(['tab-change', 'onReset'])
const reset = () => {
  emit('onReset')
}
const print = () => {
  // Create thermal printer formatted content
  const thermalContent = generateThermalReceipt()

  // Create a new window for printing
  const printWindow = window.open('', '_blank', 'width=400,height=600')
  printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt</title>
            <style>
                @media print {
                    body { 
                        font-family: 'Courier New', monospace;
                        font-size: 12px;
                        line-height: 1.2;
                        margin: 0;
                        padding: 10px;
                        width: 80mm; /* Standard thermal paper width */
                    }
                    .receipt { 
                        width: 100%;
                        text-align: center;
                    }
                    .header { 
                        border-bottom: 1px dashed #000;
                        padding-bottom: 10px;
                        margin-bottom: 10px;
                    }
                    .item { 
                        display: flex;
                        justify-content: space-between;
                        margin: 2px 0;
                    }
                    .total { 
                        border-top: 1px dashed #000;
                        padding-top: 10px;
                        margin-top: 10px;
                        font-weight: bold;
                    }
                    .footer { 
                        margin-top: 20px;
                        font-size: 10px;
                    }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            ${thermalContent}
        </body>
        </html>
    `)
  printWindow.document.close()

  // Wait for content to load then print
  printWindow.onload = () => {
    printWindow.print()
    printWindow.close()
  }
}

const generateThermalReceipt = () => {
  if (!booking.value) return ''

  const b = booking.value
  const date = new Date(b.start_date_time).toLocaleDateString()
  const time = new Date(b.services[0].start_date_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  return `
        <div class="receipt">
            <div class="header">
                <h3>${b.branch.name}</h3>
                <p>${b.branch_address?.country || ''} ${b.branch_address?.postal_code || ''}</p>
                <p>Date: ${date}</p>
                <p>Time: ${time}</p>
                <p>Receipt #: ${b.id}</p>
            </div>
            
            <div class="customer-info">
                <p><strong>Customer:</strong> ${b.user.full_name}</p>
                <p><strong>Phone:</strong> ${b.user.mobile || 'N/A'}</p>
                <p><strong>Email:</strong> ${b.user.email || 'N/A'}</p>
            </div>
            
            <div class="service-info">
                <p><strong>Staff:</strong> ${b.services[0].employee.full_name}</p>
            </div>
            
            <div class="items">
                ${b.services
                  .map(
                    (service) => `
                    <div class="item">
                        <span>${service.service_name}: </span>
                        <span>${formatCurrencyVue(service.service_price)}</span>
                    </div>
                `
                  )
                  .join('')}
            </div>
            
            ${
              b.tax
                ? `
                <div class="taxes">
                    ${b.tax
                      .map(
                        (tax) => `
                        <div class="item">
                            <span>${tax.name}</span>
                            <span>${tax.type === 'percent' ? `+${formatCurrencyVue((SUB_TOTAL_SERVICE_AMOUNT.value * tax.percent) / 100)}` : `+${formatCurrencyVue(tax.tax_amount)}`}
                            </span>
                        </div>
                    `
                      )
                      .join('')}
                </div>
            `
                : ''
            }
            
            <div class="total">
                <div class="item">
                    <span><strong>TOTAL</strong></span>
                    <span><strong>${formatCurrencyVue(finalAmount.value)}</strong></span>
                </div>
            </div>
            
            <div class="footer">
                <p>Thank you for your business!</p>
                <p>Generated on ${new Date().toLocaleString()}</p>
            </div>
        </div>
    `
}

const store = useQuickBooking()
const booking = computed(() => store.bookingResponse)
const formatCurrencyVue = (value) => {
  if (window.currencyFormat !== undefined && value) {
    return window.currencyFormat(value)
  }
  return value
}
const calculatePercentAmount = (percent) => {
  const percentAmount = (SUB_TOTAL_SERVICE_AMOUNT.value * percent) / 100

  return percentAmount
}
const SUB_TOTAL_SERVICE_AMOUNT = ref(0)
const currency_symbol = computed(() => {
  return window.defaultCurrencySymbol || ''
})
const taxAmount = computed(() => {
  let totalTaxAmount = 0

  if (booking.value.tax) {
    for (const tax of booking.value.tax) {
      if (tax.type === 'percent') {
        totalTaxAmount += (SUB_TOTAL_SERVICE_AMOUNT.value * tax.percent) / 100
      } else {
        totalTaxAmount += tax.tax_amount
      }
    }
  }

  return totalTaxAmount.toFixed(2)
})
const finalAmount = computed(() => {
  console.log(taxAmount.value)
  return Number(SUB_TOTAL_SERVICE_AMOUNT.value) + Number(taxAmount.value)
})
watch(
  () => booking,
  () => {
    if (store.bookingResponse) {
      SUB_TOTAL_SERVICE_AMOUNT.value = store.bookingResponse.services.reduce((total, service) => total + service.service_price, 0)
    } else {
      SUB_TOTAL_SERVICE_AMOUNT.value = 0
    }
  },
  { deep: true }
)
</script>
