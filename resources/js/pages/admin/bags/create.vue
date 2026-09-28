<template>
    <div class="bag-form-page">
        <div class="page-header">
            <div>
                <h1>Add Bag</h1>
                <p>Create a new bag.</p>
            </div>

            <router-link
                to="/admin/bags"
                class="btn btn-secondary"
            >
                Back
            </router-link>
        </div>

        <div class="card form-card">
            <div class="card-body">


                <form @submit.prevent="createBag">

                    <div class="mb-3">
                        <label class="form-label">
                            Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            placeholder="Bag name"
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
                            placeholder="Price"
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
                            placeholder="Color"
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
                            placeholder="Quantity"
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
                            placeholder="https://example.com/image.jpg"
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="loading"
                    >
                        {{ loading ? 'Creating...' : 'Create Bag' }}
                    </button>

                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import bagService from '../../../services/bag';

const router = useRouter();

const loading = ref(false);
const error = ref('');

const form = reactive({
    name: '',
    price: '',
    color: '',
    quantity: 0,
    image: '',
});

async function createBag() {
    loading.value = true;
    error.value = '';

    try {
        await bagService.create(form);

        router.push('/admin/bags');
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to create bag.';
    } finally {
        loading.value = false;
    }
}
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