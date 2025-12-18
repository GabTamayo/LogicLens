<script setup lang="ts">
import { MoreHorizontal, Code, Copy, Trash } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger, } from '@/components/ui/alert-dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, } from '@/components/ui/dropdown-menu'
import { toast } from 'vue-sonner';
import type { SubmissionRow } from '@/components/submissions/columns'
import { router } from '@inertiajs/vue3'

const { submission } = defineProps<{ submission: SubmissionRow }>()

defineEmits<{
    (e: 'expand'): void
}>()

function deleteSubmission(submission: SubmissionRow) {
    router.delete(`/submissions/${submission.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Submission deleted successfully')
        },
        onError: () => {
            toast.error('Failed to delete submission')
        }
    })
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
            <DropdownMenuItem @click="$emit('expand')">
                <Code class="w-4 h-4 mr-2" />
                View Code...
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <AlertDialog>
                <AlertDialogTrigger as-child>
                    <DropdownMenuItem class="text-destructive focus:text-destructive" @select.prevent>
                        <Trash class="w-4 h-4 mr-2" />
                        Remove Submission
                    </DropdownMenuItem>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently delete this
                            submission and remove the data from the server.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <Button variant="destructive" @click="deleteSubmission(submission)">Continue</Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
