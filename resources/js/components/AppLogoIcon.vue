<script setup lang="ts">
import { useAttrs } from 'vue';
const attrs = useAttrs();

defineOptions({ inheritAttrs: false });
</script>

<template>
    <svg v-bind="attrs" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <line x1="120" y1="170" x2="80" y2="110" stroke="#3b82f6" stroke-width="12" stroke-linecap="round" />
        <circle cx="75" cy="65" r="35" fill="none" stroke="#06b6d4" stroke-width="10" />
        <circle cx="75" cy="65" r="28" fill="#06b6d4" opacity="0.15" />
        <circle cx="85" cy="55" r="10" fill="#e0f2fe" opacity="0.7" />
        <circle cx="66" cy="58" r="5" fill="#e0f2fe" opacity="0.5" />
    </svg>
</template>
