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
import { toast } from 'vue-sonner';
import type { SubmissionRow } from '@/components/submissions/columns'

const { submission } = defineProps<{ submission: SubmissionRow }>()

defineEmits<{
    (e: 'expand'): void
}>()

function copy(id: string) {
    navigator.clipboard.writeText(id)
    toast('Student No. copied to clipboard')
}

function download(submission: SubmissionRow) {
    if (!submission.file_content) {
        alert('No file content found.')
        return
    }

    const studName = submission.student_name?.replace(/\s+/g, '_') || 'student'
    const fileName = `${submission.student_no}_${studName}.java`

    const blob = new Blob([submission.file_content], { type: 'text/plain' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = fileName
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
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
            <DropdownMenuItem @click="copy(submission.student_no)">
                Copy Student No.
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="$emit('expand')">
                View Code
            </DropdownMenuItem>
            <DropdownMenuItem @click="download(submission)">
                Download File
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
