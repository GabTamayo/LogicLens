<script setup lang="ts">
import type { SubmissionRow } from '@/components/submissions/columns';
import ViolationsDialog from '@/components/submissions/ViolationsDialog.vue';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
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
import { AlertCircle, Code, MoreHorizontal, Trash } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const { submission } = defineProps<{ submission: SubmissionRow }>();

defineEmits<{
    (e: 'expand'): void;
}>();

const showViolationsDialog = ref(false);

function deleteSubmission(submission: SubmissionRow) {
    router.delete(`/submissions/${submission.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Submission deleted successfully');
        },
        onError: () => {
            toast.error('Failed to delete submission');
        },
    });
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
            <DropdownMenuItem @click="$emit('expand')">
                <Code class="mr-2 h-4 w-4" />
                View Code...
            </DropdownMenuItem>
            <DropdownMenuItem @click="showViolationsDialog = true">
                <AlertCircle class="mr-2 h-4 w-4" />
                View Violations...
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <AlertDialog>
                <AlertDialogTrigger as-child>
                    <DropdownMenuItem class="text-destructive focus:text-destructive" @select.prevent>
                        <Trash class="mr-2 h-4 w-4" />
                        Remove Submission
                    </DropdownMenuItem>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently delete this submission and remove the data from the server.
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

    <!-- Violations Dialog -->
    <ViolationsDialog v-model:open="showViolationsDialog" :submission-id="submission.id" :student-name="submission.student_name" />
</template>
