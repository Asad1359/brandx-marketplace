<template>
    <div class="bag-show-page">

        <div class="page-header">
            <div>
                <h1>Bag Details</h1>
                <p>View bag information.</p>
            </div>

            <div class="actions">
                <router-link
                    :to="`/admin/bags/${route.params.id}/edit`"
                    class="btn btn-warning"
                >
                    Edit
                </router-link>

                <router-link
                    to="/admin/bags"
                    class="btn btn-secondary"
                >
                    Back
                </router-link>
            </div>
        </div>

        <div
            v-if="loading"
            class="text-center py-5"
        >
            <div class="spinner-border"></div>
            <p class="mt-2">Loading...</p>
        </div>


        <div
            v-if="bag && !loading"
            class="card details-card"
        >
            <div class="row g-0">

                <div class="col-md-5 image-section">
                    <img
                        v-if="bag.image"
                        :src="bag.image"
                        :alt="bag.name"
                        class="bag-image"
                    >

                    <div
                        v-else
                        class="no-image"
                    >
                        No Image
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card-body">

                        <h2>{{ bag.name }}</h2>

                        <hr>

                        <div class="detail-row">
                            <strong>ID:</strong>
                            <span>{{ bag.id }}</span>
                        </div>

                        <div class="detail-row">
                            <strong>Price:</strong>
                            <span>{{ bag.price }}</span>
                        </div>

                        <div class="detail-row">
                            <strong>Color:</strong>
                            <span>{{ bag.color || '-' }}</span>
                        </div>

                        <div class="detail-row">
                            <strong>Quantity:</strong>
                            <span>{{ bag.quantity ?? 0 }}</span>
                        </div>

                        <div class="detail-row">
                            <strong>Created:</strong>
                            <span>
                                {{ formatDate(bag.created_at) }}
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import bagService from '../../../services/bag';

const route = useRoute();

const bag = ref(null);
const loading = ref(false);
const error = ref('');

async function loadBag() {
    loading.value = true;
    error.value = '';

    try {
        const response = await bagService.get(route.params.id);

        const data = response.data;

        bag.value =
            data.bag ||
            data.data ||
            data;
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to load bag.';
    } finally {
        loading.value = false;
    }
}

function formatDate(date) {
    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleString();
}

onMounted(() => {
    loadBag();
});
</script>

<style scoped>
.bag-show-page {
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
    color: #6c757d;
}

.actions {
    display: flex;
    gap: 8px;
}

.details-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.image-section {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    padding: 30px;
}

.bag-image {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.no-image {
    color: #777;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid #eee;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>