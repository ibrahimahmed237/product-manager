<template>
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Product List</h1>
        <div class="space-x-4">
          <button @click="$router.push('/add-product')" class="bg-blue-500 text-white px-4 py-2 rounded">
            ADD
          </button>
          <button 
            @click="massDelete" 
            class="bg-red-500 text-white px-4 py-2 rounded"
            :disabled="selectedProducts.length === 0"
          >
            MASS DELETE
          </button>
        </div>
      </div>
  
      <div v-if="loading" class="text-center py-4">
        Loading...
      </div>
      
      <div v-else-if="error" class="text-red-500 text-center py-4">
        {{ error }}
      </div>
      
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="product in products" 
          :key="product.sku"
          class="border p-4 rounded relative"
        >
          <input
            type="checkbox"
            :value="product.id"
            v-model="selectedProducts"
            class=".delete-checkbox absolute top-4 left-4"
          />
          <div class="text-center">
            <p>{{ product.sku }}</p>
            <p>{{ product.name }}</p>
            <p>${{ formatPrice(product.price) }}</p>
            <template v-if="product.type === 'DVD'">
              <p>Size: {{ product.size }} MB</p>
            </template>
            <template v-if="product.type === 'Book'">
              <p>Weight: {{ product.weight }} KG</p>
            </template>
            <template v-if="product.type === 'Furniture'">
              <p>Dimensions: {{ product.height }}x{{ product.width }}x{{ product.length }}</p>
            </template>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue'
  
  export default {
    name: 'ProductList',
    setup() {
      const products = ref([])
      const selectedProducts = ref([])
      const loading = ref(false)
      const error = ref('')
  
      const formatPrice = (price) => {
        return Number(price).toFixed(2)
      }
  
      const fetchProducts = async () => {
        loading.value = true
        error.value = ''
        const VITE_API_URL = import.meta.env.VITE_API_URL

        try {
          const response = await fetch(`${VITE_API_URL}/products`)
          if (response.data.status === 'success') {
            products.value = response.data.data
          } else {
            error.value = 'Failed to fetch products'
          }
        } catch (err) {
          error.value = err.response?.data?.message || 'Error fetching products';
          console.log('VITE_API_URL:', VITE_API_URL);
          console.error('Error fetching products:', err)
        } finally {
          loading.value = false
        }
      }
  
      const massDelete = async () => {
        if (selectedProducts.value.length === 0) return
        
        loading.value = true
        error.value = ''
        
        try {
          await fetch(`${import.meta.env.VITE_API_URL}/mass-delete`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedProducts.value })
          });
          await fetchProducts()
          selectedProducts.value = []
        } catch (err) {
          error.value = err.response?.data?.message || 'Error deleting products'
          console.error('Error deleting products:', err)
        } finally {
          loading.value = false
        }
      }
  
      onMounted(fetchProducts)
  
      return {
        products,
        selectedProducts,
        loading,
        error,
        massDelete,
        formatPrice
      }
    }
  }
  </script>
