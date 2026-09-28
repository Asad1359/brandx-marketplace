<template>
    <aside class="sidebar" :class="{ open: modelValue }">
        <div class="sidebar-header">
            <slot name="brand">
                <span class="brand">BrandX</span>
            </slot>
        </div>

        <nav class="sidebar-nav">
            <slot />
        </nav>

        <div class="sidebar-footer">
            <slot name="footer" />
        </div>
    </aside>
</template>

<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
});
</script>

<style scoped>
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #111827;
    color: #e5e7eb;
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
}

.sidebar.open {
    transform: translateX(0);
}

@media (min-width: 769px) {
    .sidebar {
        transform: translateX(0);
    }
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.brand {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
}

.sidebar-nav {
    flex: 1;
    padding: 16px 12px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

:deep(.sidebar-item) {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 14px;
}

:deep(.sidebar-item:hover) {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
}

:deep(.sidebar-item.active) {
    background: #4f46e5;
    color: #fff;
}
</style>