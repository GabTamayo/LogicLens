<script setup lang="ts">
import type { StudentRow } from '@/components/students/columns';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { CourseShowProps } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { CircleMinus, MoreHorizontal } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const { student } = defineProps<{ student: StudentRow }>();

const page = usePage<CourseShowProps>();
const courseId = page.props.course.id;

function removeStudent() {
    router.delete(`/courses/${courseId}/students/${student.id}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.success('Student removed successfully');
        },
        onError: () => {
            toast.error('Failed to remove student');
        },
    });
}
</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 cursor-pointer p-0">
                    <span class="sr-only">Open menu</span>
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <AlertDialogTrigger as-child>
                    <DropdownMenuItem class="text-destructive focus:text-destructive" @select.prevent>
                        <CircleMinus class="mr-2 h-4 w-4" />
                        Remove Student
                    </DropdownMenuItem>
                </AlertDialogTrigger>
            </DropdownMenuContent>
        </DropdownMenu>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Remove student?</AlertDialogTitle>
                <AlertDialogDescription>
                    Are you sure you want to remove <strong>{{ student.name }}</strong> from this course? They will lose access to all activities and
                    submissions.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction class="bg-destructive hover:bg-destructive/90" @click="removeStudent"> Remove </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
