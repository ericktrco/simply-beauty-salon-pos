<template>
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
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  onProductSelect: {
    type: Function,
    required: true
  }
})

let productsModal = null
let productsDataTable = null

function initProductsDataTable() {
  if (productsDataTable) {
    productsDataTable.destroy()
  }

  productsDataTable = $('#products-datatable').DataTable({
    serverSide: false,
    ajax: {
      url: '/app/products/products_list',
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
          id: item.id ?? item.product_id ?? item.value ?? null,
          name: item.name ?? '',
          price: Number(item.min_price ?? item.price ?? item.default_price ?? 0),
          stock: Number(item.stock_qty ?? item.qty ?? 0)
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
          const disabled = row.stock <= 0 ? 'disabled' : ''
          return `<button class="btn btn-sm btn-primary add-product-btn" data-product='${JSON.stringify(row)}' ${disabled}>Add</button>`
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      search: 'Search products:',
      lengthMenu: 'Show _MENU_ products per page',
      info: 'Showing _START_ to _END_ of _TOTAL_ products',
      emptyTable: 'No products found'
    }
  })

  // Handle add product button clicks
  $('#products-datatable').on('click', '.add-product-btn', function () {
    const productData = $(this).data('product')
    props.onProductSelect(productData)
    hide()
  })
}

function show() {
  if (productsModal) {
    productsModal.show()
  }
}

function hide() {
  if (productsModal) {
    productsModal.hide()
  }
}

onMounted(() => {
  const productsEl = document.getElementById('products-modal')
  if (productsEl && window.bootstrap) {
    productsModal = new window.bootstrap.Modal(productsEl)
    productsEl.addEventListener('shown.bs.modal', function () {
      if (!productsDataTable) initProductsDataTable()
      else productsDataTable.ajax.reload()
    })
  }
})

onUnmounted(() => {
  if (productsDataTable) {
    productsDataTable.destroy()
  }
})

// Expose methods to parent
defineExpose({
  show,
  hide
})
</script>
