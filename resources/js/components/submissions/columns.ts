import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import DropdownAction from './data-table.dropdown.vue'
import { useScore } from '@/composables/useScore'

export interface SubmissionRow {
    id: string
    student_name: string
    student_email: string
    code_content: string
    language: string
    submitted_at: string
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
            const { getScoreDisplay } = useScore()
            const scoreDisplay = getScoreDisplay(score, totalScore)

            if (score === null || totalScore === null) {
                return h('div', { class: 'text-center text-xs text-muted-foreground' }, scoreDisplay.text)
            }

            return h('div', { class: `text-center font-medium ${scoreDisplay.colorClass}` }, scoreDisplay.text)
        },
    },
    {
        accessorKey: 'submitted_at',
        label: 'Submitted At',
        header: () => h('div', { class: 'text-right' }, 'Submitted At'),
        cell: ({ row }) => {
            const date = new Date(row.getValue('submitted_at'))
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
