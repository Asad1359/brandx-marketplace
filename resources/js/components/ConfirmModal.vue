<template>
    <Teleport to="body">
        <Transition name="confirm-fade">
            <div
                v-if="modelValue"
                class="confirm-overlay"
                @click.self="onCancel"
            >
                <div
                    class="confirm-modal"
                    :class="`confirm-${type}`"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="titleId"
                >

                    <!-- Icon -->
                    <div class="confirm-icon" :class="`icon-${type}`">
                        <svg
                            v-if="type === 'danger'"
                            xmlns="http://www.w3.org/2000/svg"
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>

                        <svg
                            v-else-if="type === 'warning'"
                            xmlns="http://www.w3.org/2000/svg"
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>

                        <svg
                            v-else-if="type === 'success'"
                            xmlns="http://www.w3.org/2000/svg"
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>

                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            width="28"
                            height="28"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                    </div>


                    <!-- Content -->
                    <h3 :id="titleId" class="confirm-title">
                        {{ title }}
                    </h3>

                    <p class="confirm-message">
                        <slot>{{ message }}</slot>
                    </p>


                    <!-- Actions -->
                    <div class="confirm-actions">
                        <button
                            type="button"
                            class="confirm-btn confirm-btn-cancel"
                            :disabled="loading"
                            @click="onCancel"
                        >
                            {{ cancelText }}
                        </button>

                        <button
                            type="button"
                            class="confirm-btn confirm-btn-confirm"
                            :class="`btn-${type}`"
                            :disabled="loading"
                            @click="onConfirm"
                        >
                            <span v-if="loading" class="confirm-spinner"></span>
                            <span>{{ loading ? loadingText : confirmText }}</span>
                        </button>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>


<script setup>
import { computed, watch, onUnmounted } from 'vue';

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },

    title: {
        type: String,
        default: 'Are you sure?',
    },

    message: {
        type: String,
        default: 'This action cannot be undone.',
    },

    confirmText: {
        type: String,
        default: 'Confirm',
    },

    cancelText: {
        type: String,
        default: 'Cancel',
    },

    loadingText: {
        type: String,
        default: 'Processing...',
    },

    type: {
        type: String,
        default: 'danger',
        validator: (value) =>
            ['danger', 'warning', 'success', 'info'].includes(value),
    },

    loading: {
        type: Boolean,
        default: false,
    },
});


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);


/*
|--------------------------------------------------------------------------
| Unique ID for accessibility
|--------------------------------------------------------------------------
*/

const titleId = `confirm-title-${Math.random().toString(36).slice(2, 9)}`;


/*
|--------------------------------------------------------------------------
| Handlers
|--------------------------------------------------------------------------
*/

function onConfirm() {
    if (props.loading) return;
    emit('confirm');
}

function onCancel() {
    if (props.loading) return;
    emit('cancel');
    emit('update:modelValue', false);
}


/*
|--------------------------------------------------------------------------
| Body Scroll Lock
|--------------------------------------------------------------------------
*/

watch(
    () => props.modelValue,
    (isOpen) => {
        if (typeof document === 'undefined') return;

        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
);


/*
|--------------------------------------------------------------------------
| Esc Key Handler
|--------------------------------------------------------------------------
*/

function handleEsc(event) {
    if (event.key === 'Escape' && props.modelValue) {
        onCancel();
    }
}

if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleEsc);
}

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', handleEsc);
    }

    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});
</script>


<style scoped>

/* =========================================================
   OVERLAY
========================================================= */

.confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;

    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}


/* =========================================================
   MODAL
========================================================= */

.confirm-modal {
    width: 100%;
    max-width: 420px;

    padding: 32px 28px 24px;

    background: #ffffff;
    border-radius: 18px;

    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.25);

    text-align: center;
}


/* =========================================================
   ICON
========================================================= */

.confirm-icon {
    width: 64px;
    height: 64px;

    margin: 0 auto 18px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    color: #ffffff;
}

.icon-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 10px 24px rgba(239, 68, 68, 0.30);
}

.icon-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 10px 24px rgba(245, 158, 11, 0.30);
}

.icon-success {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 10px 24px rgba(16, 185, 129, 0.30);
}

.icon-info {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    box-shadow: 0 10px 24px rgba(99, 102, 241, 0.30);
}


/* =========================================================
   TEXT
========================================================= */

.confirm-title {
    margin: 0 0 10px;

    color: #111827;

    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
}

.confirm-message {
    margin: 0 0 26px;

    color: #6b7280;

    font-size: 14px;
    line-height: 1.6;
}


/* =========================================================
   ACTIONS
========================================================= */

.confirm-actions {
    display: flex;
    gap: 10px;
}

.confirm-btn {
    flex: 1;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    height: 46px;

    border: none;
    border-radius: 10px;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition:
        transform 0.15s ease,
        background 0.2s ease,
        box-shadow 0.15s ease;
}

.confirm-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Cancel */

.confirm-btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.confirm-btn-cancel:hover:not(:disabled) {
    background: #e5e7eb;
}

/* Confirm — danger */

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.28);
}

.btn-danger:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.40);
}

/* Confirm — warning */

.btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.28);
}

.btn-warning:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.40);
}

/* Confirm — success */

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.28);
}

.btn-success:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.40);
}

/* Confirm — info */

.btn-info {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.28);
}

.btn-info:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.40);
}


/* =========================================================
   SPINNER
========================================================= */

.confirm-spinner {
    width: 14px;
    height: 14px;

    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #ffffff;

    border-radius: 50%;

    animation: confirm-spin 0.7s linear infinite;
}

@keyframes confirm-spin {
    to {
        transform: rotate(360deg);
    }
}


/* =========================================================
   TRANSITIONS
========================================================= */

.confirm-fade-enter-active {
    transition: opacity 0.2s ease;
}

.confirm-fade-leave-active {
    transition: opacity 0.15s ease;
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
    opacity: 0;
}

.confirm-fade-enter-active .confirm-modal {
    animation: confirm-pop 0.25s ease;
}

@keyframes confirm-pop {
    from {
        transform: scale(0.92);
        opacity: 0;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }
}


/* =========================================================
   DARK MODE
========================================================= */

:global(html.dark) .confirm-modal {
    background: #111827;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
}

:global(html.dark) .confirm-title {
    color: #f9fafb;
}

:global(html.dark) .confirm-message {
    color: #94a3b8;
}

:global(html.dark) .confirm-btn-cancel {
    background: #1f2937;
    color: #cbd5e1;
}

:global(html.dark) .confirm-btn-cancel:hover:not(:disabled) {
    background: #334155;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 500px) {
    .confirm-modal {
        padding: 26px 20px 20px;
    }

    .confirm-actions {
        flex-direction: column-reverse;
    }

    .confirm-btn {
        width: 100%;
    }
}

</style>