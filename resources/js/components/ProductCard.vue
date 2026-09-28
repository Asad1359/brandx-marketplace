<template>
    <div class="product-card">
        <div class="product-image-wrapper">
            <img
                :src="image || placeholder"
                :alt="name"
                class="product-image"
                @error="onError"
            />
        </div>

        <div class="product-body">
            <span v-if="seller" class="product-seller">
                {{ seller }}
            </span>

            <h3 class="product-name">
                {{ name }}
            </h3>

            <div
                v-if="rating || reviews"
                class="product-rating"
            >
                <span v-if="rating">⭐ {{ rating }}</span>
                <span v-if="rating && reviews">&nbsp;</span>
                <span v-if="reviews">({{ reviews }})</span>
            </div>

            <div class="product-footer">
                <span class="product-price">
                    {{ price || '—' }}
                </span>

                <a
                    v-if="url"
                    :href="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="product-btn"
                >
                    View
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    name: { type: String, default: 'Product' },
    price: { type: [String, Number], default: '' },
    image: { type: String, default: '' },
    url: { type: String, default: '' },
    seller: { type: String, default: '' },
    rating: { type: [String, Number], default: '' },
    reviews: { type: [String, Number], default: '' },
});

const placeholder = 'https://via.placeholder.com/400x300?text=No+Image';

const failed = ref(false);

function onError(e) {
    if (!failed.value) {
        failed.value = true;
        e.target.src = placeholder;
    }
}
</script>

<style scoped>
.product-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: 0.2s;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.product-image-wrapper {
    height: 200px;
    background: #f5f5f5;
    flex-shrink: 0;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.product-body {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-seller {
    font-size: 12px;
    color: #777;
    margin-bottom: 6px;
}

.product-name {
    font-size: 15px;
    line-height: 21px;
    font-weight: 600;
    margin: 0 0 10px;
    height: 42px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
}

.product-rating {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
}

.product-footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.product-price {
    font-weight: 700;
    font-size: 16px;
}

.product-btn {
    background: #222;
    color: white;
    padding: 7px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
}

.product-btn:hover {
    background: #000;
}

html.dark .product-card {
    background: #111827;
    border-color: #1f2937;
}

html.dark .product-image-wrapper {
    background: #0f172a;
}

html.dark .product-name {
    color: #f9fafb;
}

html.dark .product-seller {
    color: #9ca3af;
}
</style>