<template>
    <Teleport to="body">
        <Transition name="lightbox-fade">
            <div
                v-if="modelValue"
                class="lightbox-overlay"
                @click="close"
            >
                <!-- Close button -->
                <button
                    class="lightbox-close"
                    @click="close"
                    aria-label="Close"
                >
                    ×
                </button>

                <!-- Download button -->
                <a
                    class="lightbox-download"
                    :href="src"
                    download
                    target="_blank"
                    rel="noopener noreferrer"
                    @click.stop
                    aria-label="Download"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                </a>

                <!-- Image -->
                <img
                    :src="src"
                    class="lightbox-image"
                    :style="imageStyle"
                    @click.stop
                    @wheel.prevent="onWheel"
                    @mousedown="startPan"
                    @mousemove="doPan"
                    @mouseup="stopPan"
                    @mouseleave="stopPan"
                    alt="Attachment"
                />
            </div>
        </Transition>
    </Teleport>
</template>


<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    src: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const scale = ref(1);
const translateX = ref(0);
const translateY = ref(0);

let isPanning = false;
let startX = 0;
let startY = 0;

const imageStyle = computed(() => ({
    transform: `translate(${translateX.value}px, ${translateY.value}px) scale(${scale.value})`,
    cursor: isPanning ? 'grabbing' : 'grab',
    transition: isPanning ? 'none' : 'transform 0.15s ease',
}));

const close = () => {
    emit('update:modelValue', false);
    reset();
};

const reset = () => {
    scale.value = 1;
    translateX.value = 0;
    translateY.value = 0;
};

const onWheel = (event) => {
    const delta = event.deltaY > 0 ? -0.1 : 0.1;
    scale.value = Math.min(5, Math.max(0.5, scale.value + delta));
};

const startPan = (event) => {
    if (scale.value <= 1) return;
    isPanning = true;
    startX = event.clientX - translateX.value;
    startY = event.clientY - translateY.value;
};

const doPan = (event) => {
    if (!isPanning) return;
    translateX.value = event.clientX - startX;
    translateY.value = event.clientY - startY;
};

const stopPan = () => {
    isPanning = false;
};

const onKeydown = (event) => {
    if (event.key === 'Escape' && props.modelValue) {
        close();
    }
};

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            window.addEventListener('keydown', onKeydown);
        } else {
            window.removeEventListener('keydown', onKeydown);
            reset();
        }
    }
);

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});
</script>


<style scoped>

.lightbox-overlay {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(0, 0, 0, 0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: zoom-out;
}

.lightbox-image {
    max-width: 90vw;
    max-height: 90vh;
    object-fit: contain;
    user-select: none;
    cursor: grab;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-size: 28px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
    z-index: 2;
}

.lightbox-close:hover {
    background: rgba(255, 255, 255, 0.25);
}

.lightbox-download {
    position: absolute;
    top: 20px;
    right: 76px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: background 0.15s ease;
    z-index: 2;
}

.lightbox-download:hover {
    background: rgba(255, 255, 255, 0.25);
}

.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
    transition: opacity 0.2s ease;
}

.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
    opacity: 0;
}

</style>