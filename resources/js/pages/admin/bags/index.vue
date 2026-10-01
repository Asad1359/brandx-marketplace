<template>
    <div class="bags-page">
        <div class="page-header">
            <div>
                <h1>Manage Bags</h1>
                <p>View and manage all bags.</p>
            </div>

            <router-link
                to="/admin/bags/create"
                class="btn btn-primary"
            >
                + Add Bag
            </router-link>
        </div>

        <div
            v-if="loading"
            class="text-center py-5"
        >
            <div class="spinner-border"></div>
            <p class="mt-2">Loading bags...</p>
        </div>


        <div
            v-if="!loading && bags.length"
            class="card table-card"
        >
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Color</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(bag, index) in bags"
                            :key="bag.id"
                        >
                            <td>{{ index + 1 }}</td>

                            <td>
                                <img
                                    v-if="bag.image"
                                    :src="bag.image"
                                    :alt="bag.name"
                                    class="bag-image"
                                    @error="hideImage"
                                >

                                <div
                                    v-else
                                    class="no-image"
                                >
                                    No Image
                                </div>
                            </td>

                            <td>
                                <strong>{{ bag.name }}</strong>
                            </td>

                            <td>
                                {{ bag.price }}
                            </td>

                            <td>
                                {{ bag.color || '-' }}
                            </td>

                            <td>
                                {{ bag.quantity ?? 0 }}
                            </td>

                            <td>
                                <div class="action-buttons">
                                    <router-link
                                        :to="`/admin/bags/${bag.id}`"
                                        class="btn btn-sm btn-info"
                                    >
                                        View
                                    </router-link>

                                    <router-link
                                        :to="`/admin/bags/${bag.id}/edit`"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </router-link>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger"
                                        @click="deleteBag(bag.id)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="!loading && !bags.length && !error"
            class="alert alert-info"
        >
            No bags found.
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import bagService from '../../../services/bag';

const bags = ref([]);
const loading = ref(false);
const error = ref('');

async function loadBags() {
    loading.value = true;
    error.value = '';

    try {
        const response = await bagService.getAll();

        const data = response.data;

        if (Array.isArray(data)) {
            bags.value = data;
        } else if (Array.isArray(data.bags)) {
            bags.value = data.bags;
        } else if (Array.isArray(data.data)) {
            bags.value = data.data;
        } else {
            bags.value = [];
        }
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to load bags.';
    } finally {
        loading.value = false;
    }
}

async function deleteBag(id) {
    if (!confirm('Are you sure you want to delete this bag?')) {
        return;
    }

    try {
        await bagService.delete(id);

        bags.value = bags.value.filter(
            bag => bag.id !== id
        );
    } catch (err) {
        console.error(err);

        alert(
            err.response?.data?.message ||
            'Unable to delete bag.'
        );
    }
}

function hideImage(event) {
    event.target.style.display = 'none';
}

onMounted(() => {
    loadBags();
});
</script>

<style scoped>
.bags-page {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    padding: 24px;
    box-sizing: border-box;
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
    margin: 5px 0 0;
    color: #6c757d;
}

.table-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
}

.bag-image {
    width: 65px;
    height: 65px;
    object-fit: contain;
    border-radius: 8px;
}

.no-image {
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eee;
    border-radius: 8px;
    font-size: 11px;
    color: #777;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
</style>