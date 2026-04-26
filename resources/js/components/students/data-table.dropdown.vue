<script setup lang="ts">
import { MoreHorizontal, CircleMinus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import type { StudentRow } from '@/components/students/columns'
import type { CourseShowProps } from '@/types'
import { toast } from 'vue-sonner'
import { router, usePage } from '@inertiajs/vue3'

const { student } = defineProps<{ student: StudentRow }>()

const page = usePage<CourseShowProps>()
const courseId = page.props.course.id

function removeStudent() {
    router.delete(`/courses/${courseId}/students/${student.id}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.success('Student removed successfully')
        },
        onError: () => {
            toast.error('Failed to remove student')
        }
    })
}
</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="w-8 h-8 p-0 cursor-pointer">
                    <span class="sr-only">Open menu</span>
                    <MoreHorizontal class="w-4 h-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <AlertDialogTrigger as-child>
                    <DropdownMenuItem class="text-destructive focus:text-destructive" @select.prevent>
                        <CircleMinus class="w-4 h-4 mr-2" />
                        Remove Student
                    </DropdownMenuItem>
                </AlertDialogTrigger>
            </DropdownMenuContent>
        </DropdownMenu>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Remove student?</AlertDialogTitle>
                <AlertDialogDescription>
                    Are you sure you want to remove <strong>{{ student.name }}</strong> from this course?
                    They will lose access to all activities and submissions.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction class="bg-destructive hover:bg-destructive/90" @click="removeStudent">
                    Remove
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
