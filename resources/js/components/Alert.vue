<template>
    <div
        v-if="visible"
        class="alert"
        :class="`alert-${type}`"
        role="alert"
    >
        <span class="alert-icon">
            {{ icons[type] }}
        </span>

        <span class="alert-message">
            <slot>{{ message }}</slot>
        </span>

        <button
            v-if="dismissible"
            type="button"
            class="alert-close"
            @click="close"
            aria-label="Close"
        >
            ×
        </button>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: v =>
            ['success', 'error', 'warning', 'info'].includes(v),
    },
    message: { type: String, default: '' },
    dismissible: { type: Boolean, default: true },
    autoClose: { type: Number, default: 0 },
});

const emit = defineEmits(['close']);

const visible = ref(true);

const icons = {
    success: '✓',
    error: '✕',
    warning: '!',
    info: 'i',
};

watch(
    () => props.message,
    () => {
        visible.value = true;

        if (props.autoClose > 0) {
            setTimeout(() => close(), props.autoClose);
        }
    }
);

function close() {
    visible.value = false;
    emit('close');
}

if (props.autoClose > 0) {
    setTimeout(() => close(), props.autoClose);
}
</script>

<style scoped>
.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 14px;
    border: 1px solid transparent;
}

.alert-success {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}

.alert-error {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}

.alert-warning {
    background: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}

.alert-info {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1e40af;
}

.alert-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: #fff;
    flex-shrink: 0;
}

.alert-success .alert-icon {
    background: #10b981;
}

.alert-error .alert-icon {
    background: #ef4444;
}

.alert-warning .alert-icon {
    background: #f59e0b;
}

.alert-info .alert-icon {
    background: #3b82f6;
}

.alert-message {
    flex: 1;
}

.alert-close {
    background: transparent;
    border: 0;
    cursor: pointer;
    font-size: 20px;
    color: inherit;
    opacity: 0.6;
}

.alert-close:hover {
    opacity: 1;
}

html.dark .alert-success {
    background: #052e16;
    border-color: #166534;
    color: #86efac;
}

html.dark .alert-error {
    background: #450a0a;
    border-color: #991b1b;
    color: #fca5a5;
}

html.dark .alert-warning {
    background: #451a03;
    border-color: #92400e;
    color: #fcd34d;
}

html.dark .alert-info {
    background: #172554;
    border-color: #1e40af;
    color: #93c5fd;
}
</style>