<template>
  <div class="modal fade" id="staff-selection-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Select Staff for "{{ itemDescription }}"</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Current Staff</label>
            <div class="mb-2">
              <span v-for="(staff, index) in currentStaff" :key="staff.id" class="badge bg-primary me-2">
                {{ staff.name }}
                <button class="btn btn-sm btn-outline-light ms-1" @click="removeStaff(index)">
                  <i class="fas fa-times"></i>
                </button>
              </span>
              <span v-if="currentStaff.length === 0" class="text-muted"> No staff assigned yet </span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Available Staff Members</label>
            <div class="table-responsive">
              <table id="staff-datatable" class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th width="1%">Action</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  itemDescription: {
    type: String,
    required: true
  },
  currentStaff: {
    type: Array,
    default: () => []
  },
  onStaffAdd: {
    type: Function,
    required: true
  },
  onStaffRemove: {
    type: Function,
    required: true
  }
})

let staffModal = null
let staffDataTable = null

function initStaffDataTable() {
  if (staffDataTable) {
    staffDataTable.destroy()
  }

  staffDataTable = $('#staff-datatable').DataTable({
    serverSide: false,
    ajax: {
      url: '/app/employees/index_data',
      type: 'GET',
      data: function (d) {
        return {
          q: d.search ? d.search.value : ''
        }
      },
      dataSrc: function (json) {
        // Normalize the response data
        const raw = (json?.data && Array.isArray(json.data.data) && json.data.data) || (Array.isArray(json?.data) && json.data) || (Array.isArray(json?.list) && json.list) || (Array.isArray(json?.options) && json.options) || (Array.isArray(json) && json) || []

        const allStaff = raw.map((item) => ({
          id: item.id ?? item.value ?? null,
          name: item.name ?? item.label ?? (item.first_name && item.last_name ? item.first_name + ' ' + item.last_name : '') ?? ''
        }))

        // Return all staff - validation will be handled in addStaffToItem function
        return allStaff
      }
    },
    columns: [
      { data: 'name', name: 'name' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          // Check if staff is already assigned to current service
          const isAssigned = props.currentStaff.some((staff) => staff.id === row.id)

          if (isAssigned) {
            return `<button class="btn btn-sm btn-secondary" disabled>Already Assigned</button>`
          } else {
            return `<button class="btn btn-sm btn-primary add-staff-btn" data-staff='${JSON.stringify(row)}'>Add</button>`
          }
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      search: 'Search staff:',
      lengthMenu: 'Show _MENU_ staff per page',
      info: 'Showing _START_ to _END_ of _TOTAL_ staff',
      emptyTable: 'No available staff found'
    }
  })

  // Handle add staff button clicks
  $('#staff-datatable').on('click', '.add-staff-btn', function () {
    const staffData = $(this).data('staff')
    props.onStaffAdd(staffData)
    // Reload the table to update available staff
    staffDataTable.ajax.reload()
  })
}

function removeStaff(index) {
  props.onStaffRemove(index)
  // Reload the table to update available staff
  if (staffDataTable) {
    staffDataTable.ajax.reload()
  }
}

function show() {
  if (staffModal) {
    staffModal.show()
  }
}

function hide() {
  if (staffModal) {
    staffModal.hide()
  }
}

onMounted(() => {
  const staffEl = document.getElementById('staff-selection-modal')
  if (staffEl && window.bootstrap) {
    staffModal = new window.bootstrap.Modal(staffEl)
    staffEl.addEventListener('shown.bs.modal', function () {
      if (!staffDataTable) initStaffDataTable()
      else staffDataTable.ajax.reload()
    })
  }
})

onUnmounted(() => {
  if (staffDataTable) {
    staffDataTable.destroy()
  }
})

// Expose methods to parent
defineExpose({
  show,
  hide
})
</script>
