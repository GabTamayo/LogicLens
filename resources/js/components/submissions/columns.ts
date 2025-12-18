import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import DropdownAction from './data-table.dropdown.vue'

export interface SubmissionRow {
    id: string
    student_name: string
    student_email: string
    code_content: string
    language: string
    created_at: string
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
        accessorKey: 'created_at',
        label: 'Submitted At',
        header: () => h('div', { class: 'text-right' }, 'Submitted At'),
        cell: ({ row }) => {
            const date = new Date(row.getValue('created_at'))
            const formatted = date.toLocaleString('en-PH', {
                dateStyle: 'medium',
                timeStyle: 'short',
            })
            return h('div', { class: 'text-right text-xs text-muted-foreground' }, formatted)
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
