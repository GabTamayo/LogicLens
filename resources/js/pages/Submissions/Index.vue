<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem, Submission } from '@/types'
import type {
    ColumnDef,
    ColumnFiltersState,
    ExpandedState,
    SortingState,
    VisibilityState,
} from "@tanstack/vue-table"
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from "@tanstack/vue-table"
import { ArrowUpDown, ChevronDown, Ellipsis } from "lucide-vue-next"

import { h, ref, shallowRef } from "vue"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Input } from "@/components/ui/input"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import AlertDialogDelete from '@/components/AlertDialogDelete.vue'

const props = defineProps<Submission>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.name, href: `/activities/${props.activityId}/links/${props.link.id}` }
];

</script>

<template>

    <Head :title="`Submissions for ${props.link.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete
            :endpoint="`/activities/${props.activityId}/links/${props.link.id}`"
            title="Are you absolutely sure?"
            type="token"
            buttonText="Delete Token"
            />
        </template>

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="text-end me-4 mt-4">
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Submissions for {{ props.link.name }}</h4>
                <p class="text-sm text-muted-foreground">{{ props.activityTitle }}</p>
            </div>

            <div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Student Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Student No</TableHead>
                            <TableHead>Submitted At</TableHead>
                            <TableHead class="text-end"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="submission in props.submissions" :key="submission.id">
                            <TableCell class="font-medium">{{ submission.student_name }}</TableCell>
                            <TableCell>{{ submission.student_email }}</TableCell>
                            <TableCell>{{ submission.student_no }}</TableCell>
                            <TableCell>
                                {{ new Date(submission.created_at).toLocaleString() }}
                            </TableCell>
                            <TableCell class="text-end">
                                <Ellipsis />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

        </div>
    </AppLayout>
</template>
