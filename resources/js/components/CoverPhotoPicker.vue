<script setup lang="ts">
import { Item, ItemGroup, ItemHeader } from '@/components/ui/item';

defineProps<{
    coverPhotos: Array<{ name: string; path: string }>;
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

function selectCover(coverName: string) {
    emit('update:modelValue', coverName);
}
</script>

<template>
    <ItemGroup class="grid grid-cols-3">
        <Item v-for="cover in coverPhotos" :key="cover.name" as-child role="listitem" class="group relative cursor-pointer">
            <button type="button" @click="selectCover(cover.name)" class="w-full">
                <ItemHeader class="relative overflow-hidden rounded-lg">
                    <img
                        :src="cover.path"
                        :alt="cover.name"
                        class="aspect-video w-full object-cover transition-all duration-300 group-hover:scale-105 group-hover:grayscale-0"
                        :class="modelValue === cover.name ? 'ring-2 ring-primary ring-offset-2' : 'grayscale'"
                    />
                    <div v-if="modelValue === cover.name" class="absolute inset-0 flex items-center justify-center bg-black/20">
                        <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </ItemHeader>
            </button>
        </Item>
    </ItemGroup>
</template>
