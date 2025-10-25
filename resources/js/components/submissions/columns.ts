import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import DropdownAction from './data-table.dropdown.vue'
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next'
import Button from '../ui/button/Button.vue'

export interface SubmissionRow {
    id: number
    student_name: string
    student_email: string
    student_no: string
    file_path: string
    created_at: string
}

export const columns: ColumnDef<SubmissionRow>[] = [
    {
        accessorKey: 'student_name',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Student Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => h('div', { class: '' }, row.getValue('student_name')),
    },
    {
        accessorKey: 'student_email',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Email', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => h('div', { class: '' }, row.getValue('student_email')),
    },
    {
        accessorKey: 'student_no',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Student No.', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => h('div', { class: '' }, row.getValue('student_no')),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                class: 'ml-auto flex'
            }, () => ['Submitted At', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const date = new Date(row.getValue('created_at'))
            const formatted = date.toLocaleString('en-PH', {
                dateStyle: 'medium',
                timeStyle: 'short',
            })
            return h('div', { class: 'text-right text-sm' }, formatted)
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => h(DropdownAction, { submission: row.original }),
    }
]
