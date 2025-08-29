<template>
  <div class="billing card border-0 shadow-sm">
    <div class="billing-header border-bottom px-4 py-3 d-flex align-items-center gap-3">
      <div class="d-flex align-items-center gap-2">
        <div class="header-icon rounded-circle d-inline-flex align-items-center justify-content-center"><i class="fas fa-file-invoice"></i></div>
        <div>
          <div class="text-muted small">Bill Number</div>
          <a href="#" class="fw-semibold text-decoration-none">SBS01-17</a>
        </div>
      </div>
      <div class="vr d-none d-md-block"></div>
      <div class="ms-auto text-end">
        <div class="text-muted small">Sales Date</div>
        <span class="fw-medium">{{ salesDate }}</span>
      </div>
    </div>

    <div class="p-4 pb-0">
      <div class="card info-card border-0 shadow-none">
        <div class="card-body p-3">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Full Name</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-user"></i></span>
                <input class="form-control border-start-0" v-model="customerName" placeholder="Enter customer name" />
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Contact Number</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text">+971</span>
                <input class="form-control" v-model="contact" placeholder="5x xxx xxxx" />
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Membership</label>
              <Multiselect v-model="membership" :options="['Regular', 'VIP']" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 px-4 pb-4 align-items-start">
      <div class="col-lg-8">
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
          <div class="toolbar d-flex flex-wrap gap-2">
            <button class="btn btn-pill btn-primary" @click="open('service')"><i class="fas fa-concierge-bell me-2"></i>Services</button>
            <button class="btn btn-pill btn-outline-primary" @click="open('product')"><i class="fas fa-box-open me-2"></i>Products</button>
            <button class="btn btn-pill btn-outline-primary" @click="open('package')"><i class="fas fa-gift me-2"></i>Packages</button>
            <button class="btn btn-pill btn-outline-primary" @click="open('promotion')"><i class="fas fa-tags me-2"></i>Promotions</button>
          </div>
        </div>
        <div class="table-responsive table-container card border-0 shadow-sm">
          <table class="table table-modern table-striped table-hover align-middle mb-0">
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
              <tr v-for="(row, i) in items" :key="i" @click="selectedItemIndex = i" :class="{ 'row-selected': selectedItemIndex === i }">
                <td>{{ row.description }}</td>
                <td>
                  <div class="d-flex flex-column gap-2">
                    <div v-if="row.staffMembers && row.staffMembers.length > 0" class="d-flex flex-column gap-1">
                      <div v-for="(staff, staffIndex) in row.staffMembers" :key="staffIndex" class="d-flex align-items-center gap-2">
                        <span class="badge badge-staff">{{ staff.name }}</span>
                        <button class="btn btn-sm btn-outline-danger" @click="removeStaffFromItem(i, staffIndex)"><i class="fas fa-times"></i></button>
                      </div>
                    </div>
                    <button class="btn btn-sm btn-light border" @click="selectStaffForItem(i)"><i class="fas fa-user-plus me-1"></i>Add Staff</button>
                  </div>
                </td>
                <td class="text-end">{{ row.qty }}</td>
                <td class="text-end">{{ row.discountPercent }}</td>
                <td class="text-end">{{ row.discountAmount.toFixed(2) }}</td>
                <td class="text-end">{{ row.price.toFixed(2) }}</td>
                <td class="text-end">
                  <div class="input-group input-group-sm">
                    <input type="number" class="form-control" v-model="row.tip" />
                    <span class="input-group-text">AED</span>
                  </div>
                </td>
                <td class="text-end">{{ row.total.toFixed(2) }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger" @click="removeItem(i)"><i class="fas fa-trash-alt me-1"></i>Remove</button>
                </td>
              </tr>
              <tr v-if="items.length === 0">
                <td colspan="9" class="text-center text-muted">No items</td>
              </tr>
            </tbody>
            <tfoot v-if="items.length > 0">
              <tr>
                <td colspan="7"></td>
                <td class="text-end fw-semibold">Subtotal</td>
                <td class="text-end fw-semibold">AED {{ subtotal.toFixed(2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="mt-3">
          <label class="form-label">Note</label>
          <textarea class="form-control" rows="5" v-model="note" placeholder="Add a note for this bill..."></textarea>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card shadow-sm rounded-3 sticky-summary">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">Your order</h6>
              <span class="badge bg-light text-dark border">{{ items.length }} items</span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
              <span class="text-muted">Subtotal</span>
              <span>AED {{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
              <span class="text-muted">Tip</span>
              <span>AED {{ tip.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
              <span class="text-muted">Discount %</span>
              <span>{{ discountPercent }}%</span>
            </div>
            <div class="d-flex justify-content-between mb-2 small">
              <span class="text-muted">VAT</span>
              <span>AED {{ vat.toFixed(2) }}</span>
            </div>
            <hr />
            <div class="d-flex justify-content-between fw-semibold">
              <span>Total</span>
              <span class="text-danger">AED {{ grandTotal.toFixed(2) }}</span>
            </div>
            <div class="d-grid mt-3">
              <button class="btn btn-success btn-lg" @click="addPayment"><i class="fas fa-credit-card me-2"></i>Add Payment</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Components -->
    <ServicesModal ref="servicesModalRef" :onServiceSelect="addServiceToBill" />

    <ProductsModal ref="productsModalRef" :onProductSelect="addProductToBill" />

    <StaffSelectionModal ref="staffModalRef" :itemDescription="selectedItemDescription" :currentStaff="selectedItemStaff" :onStaffAdd="addStaffToItem" :onStaffRemove="removeStaffFromSelectedItem" />

    <PackagesModal ref="packagesModalRef" :onPackageSelect="addPackageToBill" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'
import ServicesModal from './ServicesModal.vue'
import ProductsModal from './ProductsModal.vue'
import StaffSelectionModal from './StaffSelectionModal.vue'
import PackagesModal from './PackagesModal.vue'

const salesDate = new Date().toLocaleDateString('en-GB')
const customerName = ref('Cash')
const contact = ref('')
const membership = ref('Regular')
const items = ref([])
const note = ref('')
const tip = ref(0)
const discountPercent = ref(0)
const vatRate = ref(0)

// Modal refs
const servicesModalRef = ref(null)
const productsModalRef = ref(null)
const staffModalRef = ref(null)
const packagesModalRef = ref(null)

// Staff selection state
const selectedItemIndex = ref(-1)
const selectedItemDescription = ref('')
const selectedItemStaff = ref([])

const subtotal = computed(() => items.value.reduce((s, i) => s + i.total, 0))
const discountAmount = computed(() => (subtotal.value * discountPercent.value) / 100)
const vat = computed(() => ((subtotal.value - discountAmount.value) * vatRate.value) / 100)
const grandTotal = computed(() => subtotal.value + tip.value - discountAmount.value + vat.value)

function addServiceToBill(service) {
  // Add service without staff - user will select staff later
  items.value.push({
    id: service.id,
    description: service.name,
    staffMembers: [], // Array to hold multiple staff members
    qty: 1,
    discountPercent: 0,
    discountAmount: 0,
    price: Number(service.price || 0),
    tip: 0,
    total: Number(service.price || 0)
  })
}

function addProductToBill(product) {
  items.value.push({
    id: product.id,
    description: product.name,
    staffMembers: [],
    qty: 1,
    discountPercent: 0,
    discountAmount: 0,
    price: Number(product.price || 0),
    tip: 0,
    total: Number(product.price || 0)
  })
}

async function addPackageToBill(pkg) {
  // Use services that already come with the package from index_list
  if (Array.isArray(pkg?.services) && pkg.services.length > 0) {
    const mapped = pkg.services.map((item) => ({
      id: item.id ?? item.service_id ?? item.value ?? null,
      name: item.name ?? item.service_name ?? item.label ?? '',
      price: Number(item.service_price ?? item.discount_price ?? item.default_price ?? item.price ?? item.min_price ?? 0)
    }))
    if (mapped.length > 0) {
      mapped.forEach((service) => addServiceToBill(service))
      return
    }
  }

  // Fallback: add the package itself as a single line item
  items.value.push({
    id: pkg.id,
    description: pkg.name,
    staffMembers: [],
    qty: 1,
    discountPercent: 0,
    discountAmount: 0,
    price: Number(pkg.price || 0),
    tip: 0,
    total: Number(pkg.price || 0)
  })
}

function selectStaffForItem(itemIndex) {
  const item = items.value[itemIndex]
  selectedItemIndex.value = itemIndex
  selectedItemDescription.value = item.description
  selectedItemStaff.value = [...(item.staffMembers || [])]

  if (staffModalRef.value) {
    staffModalRef.value.show()
  }
}

function addStaffToItem(staff) {
  if (selectedItemIndex.value >= 0) {
    if (!items.value[selectedItemIndex.value].staffMembers) {
      items.value[selectedItemIndex.value].staffMembers = []
    }

    // Check if staff is already assigned to this service
    const isAlreadyAssigned = items.value[selectedItemIndex.value].staffMembers.some((existingStaff) => existingStaff.id === staff.id)

    if (isAlreadyAssigned) {
      // Show alert for duplicate staff assignment
      alert(`Staff member "${staff.name}" is already assigned to this service.`)
      return
    }

    items.value[selectedItemIndex.value].staffMembers.push(staff)
    selectedItemStaff.value = [...items.value[selectedItemIndex.value].staffMembers]
  }
}

function removeStaffFromSelectedItem(index) {
  if (selectedItemIndex.value >= 0) {
    items.value[selectedItemIndex.value].staffMembers.splice(index, 1)
    selectedItemStaff.value = [...items.value[selectedItemIndex.value].staffMembers]
  }
}

function removeItem(index) {
  items.value.splice(index, 1)
}

function removeStaffFromItem(itemIndex, staffIndex) {
  items.value[itemIndex].staffMembers.splice(staffIndex, 1)
}

function open(type) {
  if (type === 'service' && servicesModalRef.value) {
    servicesModalRef.value.show()
  }
  if (type === 'product' && productsModalRef.value) {
    productsModalRef.value.show()
  }
  if (type === 'package' && packagesModalRef.value) {
    packagesModalRef.value.show()
  }
}

function addPayment() {}
</script>

<style scoped>
.billing .table th,
.billing .table td {
  vertical-align: middle;
}

.billing-header {
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
}

.header-icon {
  width: 40px;
  height: 40px;
  background: #0d6efd10;
  color: #0d6efd;
}

.toolbar .btn {
  min-width: 140px;
}

.btn-pill {
  border-radius: 999px;
}

.table-container thead th {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #f8f9fa;
}

.table-modern thead th {
  font-weight: 600;
  letter-spacing: 0.2px;
}

.badge-staff {
  background-color: #eef2ff;
  color: #1d4ed8;
  border: 1px solid #dbeafe;
}

.sticky-summary {
  position: sticky;
  top: 16px;
}

/* Light theme refinements */
.table-modern thead th {
  background: #f8f9fa;
}

.table-container.card {
  border-radius: 12px;
  overflow: hidden;
}

.row-selected {
  outline: 2px solid #0d6efd;
  outline-offset: -2px;
  background-color: #0d6efd0a;
}
</style>
