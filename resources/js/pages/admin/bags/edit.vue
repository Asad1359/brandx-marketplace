<template>
    <div class="bag-form-page">
        <div class="page-header">
            <div>
                <h1>Edit Bag</h1>
                <p>Update bag information.</p>
            </div>

            <router-link
                to="/admin/bags"
                class="btn btn-secondary"
            >
                Back
            </router-link>
        </div>

        <div
            v-if="loadingBag"
            class="text-center py-5"
        >
            <div class="spinner-border"></div>
            <p class="mt-2">Loading bag...</p>
        </div>


        <div
            v-if="!loadingBag"
            class="card form-card"
        >
            <div class="card-body">

                <form @submit.prevent="updateBag">

                    <div class="mb-3">
                        <label class="form-label">
                            Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Price
                        </label>

                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Color
                        </label>

                        <input
                            v-model="form.color"
                            type="text"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Quantity
                        </label>

                        <input
                            v-model="form.quantity"
                            type="number"
                            min="0"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Image URL
                        </label>

                        <input
                            v-model="form.image"
                            type="url"
                            class="form-control"
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="saving"
                    >
                        {{ saving ? 'Updating...' : 'Update Bag' }}
                    </button>

                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import bagService from '../../../services/bag';

const route = useRoute();
const router = useRouter();

const loadingBag = ref(true);
const saving = ref(false);
const error = ref('');

const form = reactive({
    name: '',
    price: '',
    color: '',
    quantity: 0,
    image: '',
});

async function loadBag() {
    loadingBag.value = true;
    error.value = '';

    try {
        const response = await bagService.get(route.params.id);

        const data = response.data;
        const bag = data.bag || data.data || data;

        form.name = bag.name ?? '';
        form.price = bag.price ?? '';
        form.color = bag.color ?? '';
        form.quantity = bag.quantity ?? 0;
        form.image = bag.image ?? '';
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to load bag.';
    } finally {
        loadingBag.value = false;
    }
}

async function updateBag() {
    saving.value = true;
    error.value = '';

    try {
        await bagService.update(
            route.params.id,
            form
        );

        router.push('/admin/bags');
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to update bag.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadBag();
});
</script>

<style scoped>
.bag-form-page {
    padding: 24px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    font-weight: 700;
    margin: 0;
}

.page-header p {
    color: #6c757d;
}

.form-card {
    max-width: 800px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
</style>