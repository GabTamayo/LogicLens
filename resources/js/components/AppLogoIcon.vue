<script setup lang="ts">
import { useAttrs } from 'vue'
const attrs = useAttrs()

defineOptions({ inheritAttrs: false })
</script>

<template>
    <svg v-bind="attrs" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M 100 45 C 70 45, 55 60, 55 80 C 55 95, 65 105, 80 110 C 95 115, 105 125, 105 140 C 105 150, 100 160, 90 165"
            stroke="#3b82f6" stroke-width="12" stroke-linecap="round" fill="none" />
        <circle cx="75" cy="65" r="28" fill="none" stroke="#06b6d4" stroke-width="10" />
        <circle cx="75" cy="65" r="23" fill="#06b6d4" opacity="0.15" />
        <circle cx="85" cy="55" r="8" fill="#e0f2fe" opacity="0.7" />
        <circle cx="66" cy="58" r="4" fill="#e0f2fe" opacity="0.5" />
    </svg>
</template>
