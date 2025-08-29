<template>
  <div class="modal fade" id="packages-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Select Packages</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table id="packages-datatable" class="table table-bordered align-middle">
              <thead>
                <tr>
                  <th>Package Name</th>
                  <th class="text-end">Price</th>
                  <th width="1%">Action</th>
                </tr>
              </thead>
            </table>
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
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  onPackageSelect: {
    type: Function,
    required: true
  }
})

let packagesModal = null
let packagesDataTable = null

function initPackagesDataTable() {
  if (packagesDataTable) {
    packagesDataTable.destroy()
  }

  packagesDataTable = $('#packages-datatable').DataTable({
    serverSide: false,
    ajax: {
      url: '/app/package/index_list',
      type: 'GET',
      data: function (d) {
        return {
          q: d.search ? d.search.value : ''
        }
      },
      dataSrc: function (json) {
        const raw = (json?.data && Array.isArray(json.data.data) && json.data.data) || (Array.isArray(json?.data) && json.data) || (Array.isArray(json?.list) && json.list) || (Array.isArray(json?.options) && json.options) || (Array.isArray(json) && json) || []

        return raw.map((item) => ({
          id: item.id ?? item.value ?? null,
          name: item.name ?? '',
          price: Number(item.package_price ?? item.price ?? item.default_price ?? 0),
          services: Array.isArray(item.services) ? item.services : []
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
        className: 'text-center',
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return `<button class="btn btn-sm btn-primary add-package-btn" data-package='${JSON.stringify(row)}'>Add</button>`
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      search: 'Search packages:',
      lengthMenu: 'Show _MENU_ packages per page',
      info: 'Showing _START_ to _END_ of _TOTAL_ packages',
      emptyTable: 'No packages found'
    }
  })

  $('#packages-datatable').on('click', '.add-package-btn', function () {
    let packageData = $(this).data('package')
    if (typeof packageData === 'string') {
      try {
        packageData = JSON.parse(packageData)
      } catch (e) {}
    }
    props.onPackageSelect(packageData)
    hide()
  })
}

function show() {
  if (packagesModal) {
    packagesModal.show()
  }
}

function hide() {
  if (packagesModal) {
    packagesModal.hide()
  }
}

onMounted(() => {
  const packagesEl = document.getElementById('packages-modal')
  if (packagesEl && window.bootstrap) {
    packagesModal = new window.bootstrap.Modal(packagesEl)
    packagesEl.addEventListener('shown.bs.modal', function () {
      if (!packagesDataTable) initPackagesDataTable()
      else packagesDataTable.ajax.reload()
    })
  }
})

onUnmounted(() => {
  if (packagesDataTable) {
    packagesDataTable.destroy()
  }
})

defineExpose({
  show,
  hide
})
</script>
