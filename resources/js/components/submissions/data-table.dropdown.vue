<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import type { SubmissionRow } from '@/components/submissions/columns'

const { submission } = defineProps<{ submission: SubmissionRow }>()

defineEmits<{
    (e: 'expand'): void
}>()

function copy(id: string) {
    navigator.clipboard.writeText(id)
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
            <DropdownMenuItem>
                View Details
            </DropdownMenuItem>
            <DropdownMenuItem @click="$emit('expand')">
                Expand
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="copy(submission.student_no)">
                Copy Student No.
            </DropdownMenuItem>
            <DropdownMenuItem>
                Download File
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
