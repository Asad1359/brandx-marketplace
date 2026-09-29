<template>
    <div
        class="voice-player"
        :class="{ 'voice-own': own }"
    >
        <!-- Play/Pause button -->
        <button
            type="button"
            class="voice-play-btn"
            @click="togglePlay"
            :aria-label="isPlaying ? 'Pause' : 'Play'"
        >
            <!-- Pause -->
            <svg
                v-if="isPlaying"
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="currentColor"
            >
                <rect x="6" y="4" width="4" height="16"/>
                <rect x="14" y="4" width="4" height="16"/>
            </svg>

            <!-- Play -->
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="currentColor"
            >
                <polygon points="6 4 20 12 6 20 6 4"/>
            </svg>
        </button>

        <!-- Body -->
        <div class="voice-body">

            <!-- Waveform + avatar for own -->
            <div class="voice-waveform-row">

                <!-- Played avatar dot for own -->
                <div
                    v-if="own"
                    class="voice-avatar-dot"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="12"
                        height="12"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>

                <!-- Bars -->
                <div class="voice-waveform">
                    <span
                        v-for="(bar, i) in bars"
                        :key="i"
                        class="voice-bar"
                        :class="{ played: i <= progressBarIndex }"
                        :style="{ height: bar + '%' }"
                    ></span>
                </div>
            </div>

            <!-- Bottom row: time + tick -->
            <div class="voice-meta">
                <span class="voice-time">
                    {{ isPlaying || progress > 0 ? formatTime(progress) : durationLabel }}
                </span>

                <span
                    v-if="own"
                    class="voice-tick"
                    :class="{ played: progress > 0 }"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
            </div>

        </div>

        <!-- Hidden real audio -->
        <audio
            ref="audioEl"
            :src="src"
            preload="metadata"
            @loadedmetadata="onLoadedMetadata"
            @timeupdate="onTimeUpdate"
            @ended="onEnded"
        ></audio>
    </div>
</template>


<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    own: {
        type: Boolean,
        default: false,
    },
});

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const audioEl = ref(null);

const isPlaying = ref(false);
const progress = ref(0);
const duration = ref(0);

const BAR_COUNT = 32;

/*
|--------------------------------------------------------------------------
| Deterministic waveform bars (based on URL)
|--------------------------------------------------------------------------
*/

const bars = computed(() => {
    const seed = props.src
        .split('')
        .reduce((acc, c) => acc + c.charCodeAt(0), 0);

    const out = [];

    for (let i = 0; i < BAR_COUNT; i++) {
        const n = Math.abs(
            Math.sin(seed + i * 0.7) * 0.5 +
            Math.sin(seed * 2 + i * 1.3) * 0.5
        );

        out.push(Math.max(25, Math.min(100, n * 70 + 30)));
    }

    return out;
});

/*
|--------------------------------------------------------------------------
| Progress index
|--------------------------------------------------------------------------
*/

const progressBarIndex = computed(() => {
    if (!duration.value) return -1;

    return Math.floor(
        (progress.value / duration.value) * BAR_COUNT
    );
});

/*
|--------------------------------------------------------------------------
| Duration label
|--------------------------------------------------------------------------
*/

const durationLabel = computed(() => {
    return formatTime(duration.value || 0);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return '0:00';

    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);

    return `${m}:${String(s).padStart(2, '0')}`;
};

/*
|--------------------------------------------------------------------------
| Play controls
|--------------------------------------------------------------------------
*/

const togglePlay = () => {
    if (!audioEl.value) return;

    if (isPlaying.value) {
        audioEl.value.pause();
    } else {
        audioEl.value.play();
    }
};

/*
|--------------------------------------------------------------------------
| Audio events
|--------------------------------------------------------------------------
*/

const onLoadedMetadata = () => {
    if (audioEl.value) {
        duration.value = audioEl.value.duration || 0;
    }
};

const onTimeUpdate = () => {
    if (audioEl.value) {
        progress.value = audioEl.value.currentTime || 0;
    }
};

const onEnded = () => {
    isPlaying.value = false;
    progress.value = 0;
};

const onPlay = () => { isPlaying.value = true; };
const onPause = () => { isPlaying.value = false; };

onMounted(() => {
    if (audioEl.value) {
        audioEl.value.addEventListener('play', onPlay);
        audioEl.value.addEventListener('pause', onPause);
    }
});

onUnmounted(() => {
    if (audioEl.value) {
        audioEl.value.removeEventListener('play', onPlay);
        audioEl.value.removeEventListener('pause', onPause);
    }
});
</script>


<style scoped>

.voice-player {
    display: flex;
    align-items: center;
    gap: 8px;

    width: 240px;
    max-width: 100%;

    padding: 6px 10px 6px 6px;

    background: #f1f5f9;
    border-radius: 10px;
}

.voice-own {
    background: rgba(255, 255, 255, 0.15);
}


/* Play button */

.voice-play-btn {
    width: 32px;
    height: 32px;

    flex-shrink: 0;

    border: none;
    border-radius: 50%;

    background: #4f46e5;
    color: #ffffff;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: transform 0.15s ease, background 0.15s ease;
}

.voice-play-btn:hover {
    transform: scale(1.06);
    background: #4338ca;
}

.voice-own .voice-play-btn {
    background: #ffffff;
    color: #4f46e5;
}

.voice-own .voice-play-btn:hover {
    background: #f1f5f9;
}


/* Body */

.voice-body {
    flex: 1;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 3px;
}


/* Waveform row */

.voice-waveform-row {
    display: flex;
    align-items: center;
    gap: 6px;

    height: 22px;
}


/* Avatar dot (for own messages only) */

.voice-avatar-dot {
    width: 18px;
    height: 18px;

    flex-shrink: 0;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.35);
    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: center;
}


/* Waveform */

.voice-waveform {
    flex: 1;
    min-width: 0;

    display: flex;
    align-items: center;
    gap: 2px;

    height: 22px;

    overflow: hidden;
}

.voice-bar {
    flex: 1;

    min-width: 2px;
    max-width: 3px;

    height: 30%;

    border-radius: 2px;

    background: #cbd5e1;

    transition: background 0.15s ease;
}

.voice-own .voice-bar {
    background: rgba(255, 255, 255, 0.35);
}

.voice-bar.played {
    background: #4f46e5;
}

.voice-own .voice-bar.played {
    background: #ffffff;
}


/* Bottom meta */

.voice-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;

    padding-left: 2px;
}

.voice-time {
    font-size: 10px;
    color: #64748b;

    font-variant-numeric: tabular-nums;
}

.voice-own .voice-time {
    color: rgba(255, 255, 255, 0.85);
}


/* Tick */

.voice-tick {
    color: #94a3b8;

    display: flex;
    align-items: center;
}

.voice-own .voice-tick {
    color: rgba(255, 255, 255, 0.55);
}

.voice-tick.played {
    color: #4f46e5;
}

.voice-own .voice-tick.played {
    color: #ffffff;
}

</style>