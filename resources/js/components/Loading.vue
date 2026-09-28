<template>
    <div
        class="loading-wrapper"
        :class="`size-${size}`"
    >
        <div class="loading-spinner"></div>

        <p v-if="text" class="loading-text">
            {{ text }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    text: {
        type: String,
        default: 'Loading...',
    },
    size: {
        type: String,
        default: 'md',
        validator: v => ['sm', 'md', 'lg'].includes(v),
    },
});
</script>

<style scoped>
.loading-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
    gap: 12px;
}

.loading-spinner {
    border: 3px solid #e5e7eb;
    border-top-color: #4f46e5;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.size-sm .loading-spinner {
    width: 20px;
    height: 20px;
    border-width: 2px;
}

.size-md .loading-spinner {
    width: 34px;
    height: 34px;
}

.size-lg .loading-spinner {
    width: 48px;
    height: 48px;
    border-width: 4px;
}

.loading-text {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

html.dark .loading-spinner {
    border-color: #374151;
    border-top-color: #60a5fa;
}

html.dark .loading-text {
    color: #9ca3af;
}
</style>