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
    score: number | null
    total_score: number | null
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
        accessorKey: 'score',
        label: 'Score',
        header: () => h('div', { class: 'text-center' }, 'Score'),
        cell: ({ row }) => {
            const score = row.getValue('score') as number | null
            const totalScore = row.original.total_score

            if (score === null || totalScore === null) {
                return h('div', { class: 'text-center text-xs text-muted-foreground' }, 'Not graded')
            }

            const percentage = totalScore > 0 ? (score / totalScore) * 100 : 0
            const colorClass = percentage >= 70 ? 'text-green-600 dark:text-green-500' :
                               percentage >= 50 ? 'text-yellow-600 dark:text-yellow-500' :
                               'text-red-600 dark:text-red-500'

            return h('div', { class: `text-center font-medium ${colorClass}` }, `${score}/${totalScore}`)
        },
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
