<script setup lang="ts">
import { ModalLink } from '@inertiaui/modal-vue'
import { Button } from '@/components/ui/button'
import { Loader, Plus } from 'lucide-vue-next'

</script>

<template>
    <ModalLink href="/activities/create" #default="{ loading }" :close-explicitly="true">
        <Button :disabled="loading">
            <Loader v-if="loading" class="h-4 w-4 animate-spin" />
            <Plus v-else /> Add Activity
        </Button>
    </ModalLink>
</template>
