<template>
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
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  onServiceSelect: {
    type: Function,
    required: true
  }
})

let servicesModal = null
let servicesDataTable = null

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
    props.onServiceSelect(serviceData)
    hide()
  })
}

function show() {
  if (servicesModal) {
    servicesModal.show()
  }
}

function hide() {
  if (servicesModal) {
    servicesModal.hide()
  }
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
})

onUnmounted(() => {
  if (servicesDataTable) {
    servicesDataTable.destroy()
  }
})

// Expose methods to parent
defineExpose({
  show,
  hide
})
</script>
