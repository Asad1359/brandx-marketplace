let audioContext = null;

/**
 * Play a short notification beep using Web Audio API.
 * No external audio file needed.
 */
export function playNotificationSound() {
    try {
        if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }

        // Resume context if suspended (browser autoplay policy)
        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }

        const now = audioContext.currentTime;

        // Two short beeps
        [0, 0.15].forEach((delay) => {
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();

            osc.connect(gain);
            gain.connect(audioContext.destination);

            osc.frequency.value = 800;
            osc.type = 'sine';

            gain.gain.setValueAtTime(0.15, now + delay);
            gain.gain.exponentialRampToValueAtTime(0.001, now + delay + 0.1);

            osc.start(now + delay);
            osc.stop(now + delay + 0.1);
        });
    } catch (e) {
        console.warn('Could not play notification sound:', e);
    }
}

/**
 * Check if user has muted notifications.
 */
export function isMuted() {
    return localStorage.getItem('chat_muted') === 'true';
}

/**
 * Toggle mute state.
 */
export function toggleMute() {
    const current = isMuted();
    localStorage.setItem('chat_muted', String(!current));
    return !current;
}