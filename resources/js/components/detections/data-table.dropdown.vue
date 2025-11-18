<script setup lang="ts">
import { MoreHorizontal, Eye, Flag } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import type { DetectionRow } from './columns'
import { ModalLink } from '@inertiaui/modal-vue';

const { detection } = defineProps<{ detection: DetectionRow }>()

function viewComparison() {
    // TODO: Open a modal or navigate to a comparison view
}

function flagDetection() {
    // TODO: Flag this detection for review
    console.log('Flag detection:', detection.id)
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0 cursor-pointer">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <ModalLink :href="`/detections/${detection.id}`">
                <DropdownMenuItem @click="viewComparison">
                    <Eye class="w-4 h-4 mr-2" />
                    View Comparison
                </DropdownMenuItem>
            </ModalLink>
            <DropdownMenuItem @click="flagDetection">
                <Flag class="w-4 h-4 mr-2" />
                Flag Detection
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
