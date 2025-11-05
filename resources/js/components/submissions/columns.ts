import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import DropdownAction from './data-table.dropdown.vue'

export interface SubmissionRow {
    id: number
    student_name: string
    student_email: string
    student_no: string
    file_path: string
    created_at: string
    file_content?: string
    file_extension?: string
}

export const columns: ColumnDef<SubmissionRow>[] = [
    {
        accessorKey: 'student_name',
        label: 'Student Name',
        header: () => h('div', { class: '' }, 'Student Name'),
        cell: ({ row }) => h('div', { class: '' }, row.getValue('student_name')),
    },
    {
        accessorKey: 'student_email',
        label: 'Email',
        header: () => h('div', { class: '' }, 'Student Email'),
        cell: ({ row }) => h('div', { class: '' }, row.getValue('student_email')),
    },
    {
        accessorKey: 'student_no',
        label: 'Student No',
        header: () => h('div', { class: '' }, 'Student No.'),
        cell: ({ row }) => h('div', { class: '' }, row.getValue('student_no')),
    },
    {
        accessorKey: 'created_at',
        label: 'Submitted At',
        header: () => h('div', { class: 'text-right' }, 'Submitted At'),
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
        cell: ({ row }) => {
            const submission = row.original

            return h('div', { class: 'relative' }, h(DropdownAction, {
                submission,
                onExpand: row.toggleExpanded,
            }))
        },
    }
]
