<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { router } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue';
import { Eye, Flag, FlagOff, MoreHorizontal } from 'lucide-vue-next';
import type { DetectionRow } from './columns';

const { detection } = defineProps<{ detection: DetectionRow }>();

function flagDetection() {
    router.patch(`/detections/${detection.id}/flag`);
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 cursor-pointer p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <ModalLink :href="`/detections/${detection.id}`" position="top">
                <DropdownMenuItem>
                    <Eye class="mr-2 h-4 w-4" />
                    View Comparison
                </DropdownMenuItem>
            </ModalLink>
            <DropdownMenuItem @click="flagDetection">
                <component :is="detection.flagged ? FlagOff : Flag" class="mr-2 h-4 w-4 text-destructive" />
                {{ detection.flagged ? 'Remove Flag' : 'Flag Detection' }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
