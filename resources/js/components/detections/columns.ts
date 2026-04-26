import { Badge } from '@/components/ui/badge';
import { useSimilarity } from '@/composables/useSimilarity';
import type { ColumnDef } from '@tanstack/vue-table';
import { Flag } from 'lucide-vue-next';
import { h } from 'vue';
import DropdownAction from './data-table.dropdown.vue';

export interface DetectionRow {
    id: string;
    flagged?: boolean;
    submission_a: {
        id: number;
        student_name: string;
        student_email: string;
    };
    submission_b: {
        id: number;
        student_name: string;
        student_email: string;
    };
    avg_score: number;
    created_at: string;
}

const { getSimilarityBadge } = useSimilarity();

const getSimilarityBadgeVNode = (score: number) => {
    const badge = getSimilarityBadge(score);
    return h(Badge, { variant: badge.variant }, () => badge.label);
};

export const columns: ColumnDef<DetectionRow>[] = [
    {
        id: 'flag',
        label: 'Flag',
        header: () => h('div', { class: 'text-center' }, ''),
        cell: ({ row }) => {
            const flagged = !!row.original.flagged;
            return h('div', { class: 'flex justify-center' }, flagged ? h(Flag, { class: 'w-4 h-4 text-destructive', title: 'Flagged' }) : undefined);
        },
        enableHiding: false,
        size: 36,
    },
    {
        accessorKey: 'submission_a.student_name',
        label: 'Student A',
        header: () => h('div', { class: '' }, 'Student A'),
        cell: ({ row }) => {
            const studentA = row.original.submission_a;
            return h('div', { class: 'flex flex-col' }, [h('span', { class: 'font-medium' }, studentA.student_name)]);
        },
    },
    {
        accessorKey: 'submission_b.student_name',
        label: 'Student B',
        header: () => h('div', { class: '' }, 'Student B'),
        cell: ({ row }) => {
            const studentB = row.original.submission_b;
            return h('div', { class: 'flex flex-col' }, [h('span', { class: 'font-medium' }, studentB.student_name)]);
        },
    },
    {
        accessorKey: 'avg_score',
        label: 'Similarity Score',
        header: () => h('div', { class: 'text-center' }, 'Similarity Score'),
        cell: ({ row }) => {
            const score = row.getValue('avg_score') as number;
            return h('div', { class: 'text-center font-mono font-semibold' }, `${(score * 100).toFixed(2)}%`);
        },
    },
    {
        accessorKey: 'level',
        label: 'Level',
        header: () => h('div', { class: 'text-center' }, 'Level'),
        cell: ({ row }) => {
            const score = row.original.avg_score;
            return h('div', { class: 'flex justify-center' }, getSimilarityBadgeVNode(score));
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const detection = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    detection,
                }),
            );
        },
    },
];
