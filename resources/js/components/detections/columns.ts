import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import DropdownAction from './data-table.dropdown.vue'
import { Badge } from '@/components/ui/badge'

export interface DetectionRow {
    id: string
    submission_a: {
        id: number
        student_name: string
        student_no: string
        student_email: string
    }
    submission_b: {
        id: number
        student_name: string
        student_no: string
        student_email: string
    }
    similarity_score: number
    created_at: string
}

const getSimilarityBadge = (score: number) => {
    if (score >= 0.9) {
        return h(Badge, { variant: 'destructive' }, () => 'Very High')
    }
    if (score >= 0.85) {
        return h(Badge, { variant: 'default' }, () => 'High')
    }
    if (score >= 0.8) {
        return h(Badge, { variant: 'secondary' }, () => 'Medium')
    }
    return h(Badge, { variant: 'outline' }, () => 'Low')
}

export const columns: ColumnDef<DetectionRow>[] = [
    {
        accessorKey: 'submission_a.student_name',
        label: 'Student A',
        header: () => h('div', { class: '' }, 'Student A'),
        cell: ({ row }) => {
            const studentA = row.original.submission_a
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, studentA.student_name),
                h('span', { class: 'text-xs text-muted-foreground' }, studentA.student_no),
            ])
        },
    },
    {
        accessorKey: 'submission_b.student_name',
        label: 'Student B',
        header: () => h('div', { class: '' }, 'Student B'),
        cell: ({ row }) => {
            const studentB = row.original.submission_b
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, studentB.student_name),
                h('span', { class: 'text-xs text-muted-foreground' }, studentB.student_no),
            ])
        },
    },
    {
        accessorKey: 'similarity_score',
        label: 'Similarity Score',
        header: () => h('div', { class: 'text-center' }, 'Similarity Score'),
        cell: ({ row }) => {
            const score = row.getValue('similarity_score') as number
            return h('div', { class: 'text-center font-mono font-semibold' }, `${(score * 100).toFixed(2)}%`)
        },
    },
    {
        accessorKey: 'level',
        label: 'Level',
        header: () => h('div', { class: 'text-center' }, 'Level'),
        cell: ({ row }) => {
            const score = row.original.similarity_score
            return h('div', { class: 'flex justify-center' }, getSimilarityBadge(score))
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const detection = row.original

            return h('div', { class: 'relative' }, h(DropdownAction, {
                detection,
            }))
        },
    }
]
