<template>
    <div class="ratings-page">
        <h1>Chat Ratings</h1>
        <p>Customer feedback on support conversations.</p>

        <div class="stats">
            <div class="stat">
                <span>Average Rating</span>
                <strong>{{ average }} ★</strong>
            </div>
            <div class="stat">
                <span>Total Ratings</span>
                <strong>{{ total }}</strong>
            </div>
        </div>

        <div class="ratings-list">
            <div
                v-for="rating in ratings"
                :key="rating.id"
                class="rating-item"
            >
                <div class="rating-user">
                    <strong>{{ rating.user?.name }}</strong>
                    <small>{{ rating.user?.email }}</small>
                </div>

                <div class="rating-stars">
                    <span v-for="i in 5" :key="i" :class="{ filled: i <= rating.rating }">
                        ★
                    </span>
                </div>

                <p v-if="rating.feedback" class="rating-feedback">
                    {{ rating.feedback }}
                </p>

                <small class="rating-date">
                    {{ new Date(rating.created_at).toLocaleString() }}
                </small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getAdminRatings } from '../../services/chat';

const ratings = ref([]);
const average = ref(0);
const total = ref(0);

onMounted(async () => {
    try {
        const response = await getAdminRatings();
        if (response.data?.success) {
            ratings.value = response.data.ratings;
            average.value = response.data.average;
            total.value = response.data.total;
        }
    } catch (error) {
        console.error(error);
    }
});
</script>

<style scoped>
.ratings-page { padding: 24px; }
.ratings-page h1 { margin: 0 0 6px; }
.ratings-page p { color: #6b7280; margin: 0 0 24px; }
.stats { display: flex; gap: 16px; margin-bottom: 24px; }
.stat { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; min-width: 180px; }
.stat span { display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px; }
.stat strong { font-size: 24px; color: #111827; }
.ratings-list { display: grid; gap: 12px; }
.rating-item { background: white; padding: 16px; border-radius: 12px; border: 1px solid #e5e7eb; }
.rating-user strong { display: block; }
.rating-user small { color: #6b7280; font-size: 12px; }
.rating-stars { color: #d1d5db; font-size: 20px; margin: 8px 0; }
.rating-stars .filled { color: #f59e0b; }
.rating-feedback { margin: 8px 0; color: #374151; font-size: 14px; }
.rating-date { color: #9ca3af; font-size: 11px; }
</style>