<script setup lang="ts">
import { ref } from 'vue'
import { ModalLink } from '@inertiaui/modal-vue'
import { Button } from '@/components/ui/button'
import { LoaderCircle, Plus } from 'lucide-vue-next'

const isLoading = ref(false)

function handleClick() {
    isLoading.value = true
}

function resetLoading() {
    isLoading.value = false
}
</script>

<template>
    <ModalLink href="/activities/create" @click="handleClick" @close="resetLoading"
        :class="{ 'pointer-events-none opacity-50': isLoading }">
        <Button>
            <LoaderCircle v-if="isLoading" class="h-4 w-4 animate-spin" />
            <Plus v-else /> Add Activity
        </Button>
    </ModalLink>
</template>
