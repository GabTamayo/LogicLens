// @/components/student-course/students/columns.ts
import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'

export interface StudentRow {
    id: number
    name: string
    email: string
}

export const columns: ColumnDef<StudentRow>[] = [
    {
        accessorKey: 'name',
        label: 'Name',
        header: () => h('div', { class: '' }, 'Name'),
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
    },
    {
        accessorKey: 'email',
        label: 'Email',
        header: () => h('div', { class: '' }, 'Email'),
        cell: ({ row }) => h('div', { class: 'text-sm text-muted-foreground' }, row.getValue('email')),
    },
]
