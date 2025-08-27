<template>
  <div class="billing card p-5">
    <div class="row g-3 align-items-center mb-3">
      <div class="col-md-3">
        <div>
          <small class="text-muted">Bill Number:</small>
          <a href="#" class="fw-semibold"> SBS01-17</a>
        </div>
      </div>
      <div class="col-md-3 ms-auto text-end">
        <small class="text-muted">Sales Date:</small>
        <span>{{ salesDate }}</span>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Full Name</label>
        <input class="form-control" v-model="customerName" />
      </div>
      <div class="col-md-4">
        <label class="form-label">Contact Number</label>
        <div class="input-group">
          <span class="input-group-text">+971</span>
          <input class="form-control" v-model="contact" />
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Membership</label>
        <Multiselect v-model="membership" :options="['Regular', 'VIP']" />
      </div>
    </div>

    <div class="d-flex gap-2 mb-2">
      <button class="btn btn-primary" @click="open('service')">Services</button>
      <button class="btn btn-primary" @click="open('product')">Products</button>
      <button class="btn btn-primary" @click="open('package')">Packages</button>
      <button class="btn btn-primary" @click="open('promotion')">Promotions</button>
    </div>

    <div class="table-responsive mb-3">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Description</th>
            <th>Staff</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Disc(%)</th>
            <th class="text-end">Disc(AED)</th>
            <th class="text-end">Price</th>
            <th class="text-end">Tip</th>
            <th class="text-end">Total</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, i) in items" :key="i">
            <td>{{ row.description }}</td>
            <td>
              <div class="d-flex flex-column gap-2">
                <!-- Staff list -->
                <div v-if="row.staffMembers && row.staffMembers.length > 0" class="d-flex flex-column gap-1">
                  <div v-for="(staff, staffIndex) in row.staffMembers" :key="staffIndex" class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary">{{ staff.name }}</span>
                    <button class="btn btn-sm btn-outline-danger" @click="removeStaffFromItem(i, staffIndex)">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <!-- Add staff button -->
                <button class="btn btn-sm btn-outline-primary" @click="selectStaffForItem(i)"><i class="fas fa-plus"></i> Add Staff</button>
              </div>
            </td>
            <td class="text-end">{{ row.qty }}</td>
            <td class="text-end">{{ row.discountPercent }}</td>
            <td class="text-end">{{ row.discountAmount.toFixed(2) }}</td>
            <td class="text-end">{{ row.price.toFixed(2) }}</td>
            <td class="text-end">{{ row.tip.toFixed(2) }}</td>
            <td class="text-end">{{ row.total.toFixed(2) }}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-danger" @click="removeItem(i)">Remove</button>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="9" class="text-center text-muted">No items</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Note</label>
        <textarea class="form-control" rows="6" v-model="note"></textarea>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3">Payment Details</h6>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Subtotal</span>
              <span>AED {{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Tip</span>
              <span>AED {{ tip.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Discount %</span>
              <span>{{ discountPercent }}%</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">VAT</span>
              <span>AED {{ vat.toFixed(2) }}</span>
            </div>
            <hr />
            <div class="d-flex justify-content-between fw-semibold">
              <span>Total</span>
              <span class="text-danger">AED {{ grandTotal.toFixed(2) }}</span>
            </div>
            <div class="d-grid mt-3">
              <button class="btn btn-primary" @click="addPayment">Add Payment</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Services Modal -->
    <div class="modal fade" id="services-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Select Services</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table id="services-datatable" class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th class="text-end">Price</th>
                    <th width="1%">Action</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /Services Modal -->

    <!-- Products Modal -->
    <div class="modal fade" id="products-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Select Products</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table id="products-datatable" class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Stock</th>
                    <th width="1%">Action</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /Products Modal -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'
import { useRequest } from '@/helpers/hooks/useCrudOpration'

const { listingRequest } = useRequest()

const salesDate = new Date().toLocaleDateString('en-GB')
const customerName = ref('Cash')
const contact = ref('')
const membership = ref('Regular')
const items = ref([])
const note = ref('')
const tip = ref(0)
const discountPercent = ref(0)
const vatRate = ref(0)

let servicesModal = null
let servicesDataTable = null
let productsModal = null
let productsDataTable = null

const subtotal = computed(() => items.value.reduce((s, i) => s + i.total, 0))
const discountAmount = computed(() => (subtotal.value * discountPercent.value) / 100)
const vat = computed(() => ((subtotal.value - discountAmount.value) * vatRate.value) / 100)
const grandTotal = computed(() => subtotal.value + tip.value - discountAmount.value + vat.value)

function initServicesDataTable() {
  if (servicesDataTable) {
    servicesDataTable.destroy()
  }

  servicesDataTable = $('#services-datatable').DataTable({
    serverSide: false,
    ajax: {
      url: '/app/services/index_list',
      type: 'GET',
      data: function (d) {
        return {
          q: d.search ? d.search.value : ''
        }
      },
      dataSrc: function (json) {
        // Normalize the response data
        const raw = (json?.data && Array.isArray(json.data.data) && json.data.data) || (Array.isArray(json?.data) && json.data) || (Array.isArray(json?.list) && json.list) || (Array.isArray(json?.options) && json.options) || (Array.isArray(json) && json) || []

        return raw.map((item) => ({
          id: item.id ?? item.value ?? null,
          name: item.name ?? item.label ?? '',
          price: Number(item.default_price ?? item.price ?? item.min_price ?? 0)
        }))
      }
    },
    columns: [
      { data: 'name', name: 'name' },
      {
        data: 'price',
        name: 'price',
        className: 'text-end',
        render: function (data) {
          return parseFloat(data || 0).toFixed(2)
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          return `<button class="btn btn-sm btn-primary add-service-btn" data-service='${JSON.stringify(row)}'>Add</button>`
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      search: 'Search services:',
      lengthMenu: 'Show _MENU_ services per page',
      info: 'Showing _START_ to _END_ of _TOTAL_ services',
      emptyTable: 'No services found'
    }
  })

  // Handle add service button clicks
  $('#services-datatable').on('click', '.add-service-btn', function () {
    const serviceData = $(this).data('service')
    addServiceToBill(serviceData)
    // Don't close services modal here - it will be closed after staff selection
  })
}

function addServiceToBill(srv) {
  // Add service without staff - user will select staff later
  items.value.push({
    id: srv.id,
    description: srv.name,
    staffMembers: [], // Array to hold multiple staff members
    qty: 1,
    discountPercent: 0,
    discountAmount: 0,
    price: Number(srv.price || 0),
    tip: 0,
    total: Number(srv.price || 0)
  })

  // Close services modal
  servicesModal.hide()
}

function selectStaffForItem(itemIndex) {
  // Show staff selection modal for specific item
  showStaffSelectionModalForItem(itemIndex)
}

function showStaffSelectionModalForItem(itemIndex) {
  const item = items.value[itemIndex]

  // Create a temporary modal for staff selection
  const modalHtml = `
    <div class="modal fade" id="staff-selection-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Select Staff for "${item.description}"</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Current Staff</label>
              <div id="current-staff-list" class="mb-2">
                <!-- Current staff will be displayed here -->
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Add New Staff Member</label>
              <select id="staff-select" class="form-control">
                <option value="">Choose staff...</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="confirm-staff-btn">Add Staff</button>
          </div>
        </div>
      </div>
    </div>
  `

  // Remove existing modal if any
  const existingModal = document.getElementById('staff-selection-modal')
  if (existingModal) {
    existingModal.remove()
  }

  // Add modal to body
  document.body.insertAdjacentHTML('beforeend', modalHtml)

  // Initialize modal
  const staffModal = new window.bootstrap.Modal(document.getElementById('staff-selection-modal'))

  // Load staff data
  loadStaffData().then((staffList) => {
    const staffSelect = document.getElementById('staff-select')
    const currentStaffList = document.getElementById('current-staff-list')

    // Display current staff
    if (item.staffMembers && item.staffMembers.length > 0) {
      currentStaffList.innerHTML = item.staffMembers.map((staff) => `<span class="badge bg-primary me-2">${staff.name}</span>`).join('')
    } else {
      currentStaffList.innerHTML = '<span class="text-muted">No staff assigned yet</span>'
    }

    // Populate dropdown with available staff (excluding already assigned)
    const assignedStaffIds = item.staffMembers ? item.staffMembers.map((s) => s.id) : []
    const availableStaff = staffList.filter((staff) => !assignedStaffIds.includes(staff.id))

    availableStaff.forEach((staff) => {
      const option = document.createElement('option')
      option.value = staff.id
      option.textContent = staff.name
      staffSelect.appendChild(option)
    })
  })

  // Handle confirm button
  document.getElementById('confirm-staff-btn').addEventListener('click', function () {
    const selectedStaffId = document.getElementById('staff-select').value
    const selectedStaffName = document.getElementById('staff-select').options[document.getElementById('staff-select').selectedIndex].text

    if (selectedStaffId) {
      // Add staff to the array
      if (!items.value[itemIndex].staffMembers) {
        items.value[itemIndex].staffMembers = []
      }

      items.value[itemIndex].staffMembers.push({
        id: selectedStaffId,
        name: selectedStaffName
      })

      // Close modal
      staffModal.hide()
    } else {
      alert('Please select a staff member')
    }
  })

  // Show the modal
  staffModal.show()
}

async function loadStaffData() {
  try {
    const response = await fetch('/app/employees/index_data')
    const data = await response.json()

    // Normalize the response data - employees index_data returns different structure
    const raw = (data?.data && Array.isArray(data.data.data) && data.data.data) || (Array.isArray(data?.data) && data.data) || (Array.isArray(data?.list) && data.list) || (Array.isArray(data?.options) && data.options) || (Array.isArray(data) && data) || []

    return raw.map((item) => ({
      id: item.id ?? item.value ?? null,
      name: item.name ?? item.label ?? (item.first_name && item.last_name ? item.first_name + ' ' + item.last_name : '') ?? ''
    }))
  } catch (error) {
    console.error('Error loading staff data:', error)
    return []
  }
}

function removeItem(index) {
  items.value.splice(index, 1)
}

function removeStaffFromItem(itemIndex, staffIndex) {
  items.value[itemIndex].staffMembers.splice(staffIndex, 1)
}

function initProductsDataTable() {
  if (productsDataTable) {
    productsDataTable.destroy()
  }

  productsDataTable = $('#products-datatable').DataTable({
    serverSide: false,
    ajax: {
      url: '/app/products/index_data',
      type: 'GET',
      data: function (d) {
        return {
          q: d.search ? d.search.value : ''
        }
      },
      dataSrc: function (json) {
        // Expect index_data returns datatable format {data: [...]} or array
        const arr = (Array.isArray(json?.data) && json.data) || (Array.isArray(json) && json) || []
        return arr.map((p) => ({
          id: p.id ?? p.product_id ?? p.value ?? null,
          name: p.name ?? '',
          price: Number(p.min_price ?? p.price ?? p.default_price ?? 0),
          stock: Number(p.stock_qty ?? p.qty ?? 0)
        }))
      }
    },
    columns: [
      { data: 'name', name: 'name' },
      {
        data: 'price',
        name: 'price',
        className: 'text-end',
        render: function (data) {
          return parseFloat(data || 0).toFixed(2)
        }
      },
      { data: 'stock', name: 'stock', className: 'text-end' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          return `<button class="btn btn-sm btn-primary add-product-btn" data-product='${JSON.stringify(row)}'>Add</button>`
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      search: 'Search products:',
      emptyTable: 'No products found'
    }
  })

  // Handle add product button
  $('#products-datatable').on('click', '.add-product-btn', function () {
    const productData = $(this).data('product')
    addProductToBill(productData)
  })
}

function addProductToBill(prod) {
  items.value.push({
    id: prod.id,
    description: prod.name,
    staffMembers: [],
    qty: 1,
    discountPercent: 0,
    discountAmount: 0,
    price: Number(prod.price || 0),
    tip: 0,
    total: Number(prod.price || 0)
  })
  if (productsModal) productsModal.hide()
}

onMounted(() => {
  const servicesEl = document.getElementById('services-modal')
  if (servicesEl && window.bootstrap) {
    servicesModal = new window.bootstrap.Modal(servicesEl)
    servicesEl.addEventListener('shown.bs.modal', function () {
      if (!servicesDataTable) initServicesDataTable()
      else servicesDataTable.ajax.reload()
    })
  }
  const productsEl = document.getElementById('products-modal')
  if (productsEl && window.bootstrap) {
    productsModal = new window.bootstrap.Modal(productsEl)
    productsEl.addEventListener('shown.bs.modal', function () {
      if (!productsDataTable) initProductsDataTable()
      else productsDataTable.ajax.reload()
    })
  }
})

function open(type) {
  if (type === 'service' && servicesModal) servicesModal.show()
  if (type === 'product' && productsModal) productsModal.show()
}

function addPayment() {}
</script>

<style scoped>
.billing .table th,
.billing .table td {
  vertical-align: middle;
}
</style>
