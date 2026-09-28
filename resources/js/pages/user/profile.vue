<template>
    <div class="profile-page">

        <div class="page-header">
            <h1>My Profile</h1>
            <p>Manage your account information.</p>
        </div>

        <div
            v-if="success"
            class="alert alert-success"
        >
            {{ success }}
        </div>

        <div
            v-if="error"
            class="alert alert-danger"
        >
            {{ error }}
        </div>

        <div class="card profile-card">
            <div class="card-body">

                <form @submit.prevent="updateProfile">

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
                            Email
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="loading"
                    >
                        {{ loading ? 'Saving...' : 'Save Changes' }}
                    </button>

                </form>

            </div>
        </div>

    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '../../services/api';

const loading = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
    name: '',
    email: '',
});

async function loadProfile() {
    try {
        const response = await api.get('/profile');

        const data =
            response.data?.user ||
            response.data?.data ||
            response.data;

        form.name = data.name ?? '';
        form.email = data.email ?? '';
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to load profile.';
    }
}

async function updateProfile() {
    loading.value = true;
    error.value = '';
    success.value = '';

    try {
        await csrf();

        await api.put('/profile', {
            name: form.name,
            email: form.email,
        });

        success.value =
            'Profile updated successfully.';
    } catch (err) {
        console.error(err);

        error.value =
            err.response?.data?.message ||
            'Unable to update profile.';
    } finally {
        loading.value = false;
    }
}

onMounted(loadProfile);
</script>

<style scoped>
.profile-page {
    padding: 24px;
}

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
}

.page-header p {
    color: #6c757d;
    margin-top: 5px;
}

.profile-card {
    max-width: 800px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
}
</style>