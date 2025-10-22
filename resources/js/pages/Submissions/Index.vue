<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem, Submission } from '@/types'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination"
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
            <AlertDialogDelete :endpoint="`/activities/${props.activityId}/links/${props.link.id}`" type="token"
                buttonText="Delete Token" />
        </template>

        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Submissions for {{ props.link.name }}
                    </h4>
                    <p class="text-sm text-muted-foreground">{{ props.activityTitle }}</p>
                </div>
                <div class="mt-4 text-center">
                    <Button class="cursor-pointer">Detect Submission</Button>
                </div>
            </div>

            <!--Use Data Table format Here check this out https://www.shadcn-vue.com/docs/components/data-table.html for reference-->
            <div class="flex-1 overflow-y-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Student Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Student No</TableHead>
                            <TableHead>Submitted At</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="props.submissions.data.length > 0">
                            <TableRow v-for="submission in props.submissions.data" :key="submission.id">
                                <TableCell class="font-medium">{{ submission.student_name }}</TableCell>
                                <TableCell>{{ submission.student_email }}</TableCell>
                                <TableCell>{{ submission.student_no }}</TableCell>
                                <TableCell>
                                    {{ new Date(submission.created_at).toLocaleString() }}
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="5" class="text-center text-muted-foreground py-6">
                                    No submissions found.
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
