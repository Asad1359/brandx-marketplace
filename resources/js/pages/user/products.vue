<template>
    <div class="products-page">

        <div class="page-header">
            <div>
                <h1>Products</h1>
                <p>Browse available bags and products.</p>
            </div>

            <button
                type="button"
                class="btn btn-primary"
                @click="loadProducts"
                :disabled="loading"
            >
                {{ loading ? 'Loading...' : 'Refresh' }}
            </button>
        </div>

        <div
            v-if="error"
            class="alert alert-danger"
        >
            {{ error }}
        </div>

        <div
            v-if="loading"
            class="text-center py-5"
        >
            <div class="spinner-border"></div>
            <p class="mt-2">Loading products...</p>
        </div>

        <div
            v-if="!loading && products.length"
            class="row g-4"
        >
            <div
                v-for="(product, index) in products"
                :key="product.id || index"
                class="col-sm-6 col-md-4 col-lg-3"
            >
                <div class="card product-card h-100">

                    <img
                        v-if="product.image"
                        :src="product.image"
                        :alt="product.name"
                        class="product-image"
                        @error="hideImage"
                    >

                    <div
                        v-else
                        class="no-image"
                    >
                        No Image
                    </div>

                    <div class="card-body d-flex flex-column">

                        <h5 class="card-title">
                            {{ product.name || 'Unnamed Product' }}
                        </h5>

                        <p class="price">
                            {{ product.price ?? 'N/A' }}
                        </p>

                        <p class="text-muted mb-2">
                            Color:
                            {{ product.color || '-' }}
                        </p>

                        <p class="text-muted">
                            Quantity:
                            {{ product.quantity ?? 0 }}
                        </p>

                        <router-link
                            :to="`/bags/${product.id}`"
                            class="btn btn-outline-primary mt-auto"
                        >
                            View Details
                        </router-link>

                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="!loading && !products.length && !error"
            class="alert alert-info"
        >
            No products found.
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import bagService from '../../services/bag';

const products = ref([]);
const loading = ref(false);
const error = ref('');

async function loadProducts() {
    loading.value = true;
    error.value = '';

    try {
        const response = await bagService.getAll();

        const data = response.data;

        if (Array.isArray(data)) {
            products.value = data;
        } else if (Array.isArray(data.bags)) {
            products.value = data.bags;
        } else if (Array.isArray(data.data)) {
            products.value = data.data;
        } else {
            products.value = [];
        }
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to load products.';
    } finally {
        loading.value = false;
    }
}

function hideImage(event) {
    event.target.style.display = 'none';
}

onMounted(loadProducts);
</script>

<style scoped>
.products-page {
    padding: 24px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
}

.page-header p {
    margin-top: 5px;
    color: #6c757d;
}

.product-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
    transition: transform .2s ease;
}

.product-card:hover {
    transform: translateY(-3px);
}

.product-image {
    width: 100%;
    height: 220px;
    object-fit: contain;
    background: #f8f9fa;
    padding: 10px;
}

.no-image {
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eee;
    color: #777;
}

.card-title {
    font-size: 17px;
    line-height: 1.4;
}

.price {
    font-size: 19px;
    font-weight: 700;
}
</style>