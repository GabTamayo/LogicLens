import type { ColumnDef } from '@tanstack/vue-table';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { h } from 'vue';
import DropdownAction from './data-table.dropdown.vue';

dayjs.extend(relativeTime);

export interface StudentRow {
    id: number;
    name: string;
    email: string;
    enrolled_at: string;
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
    {
        accessorKey: 'enrolled_at',
        label: 'Enrolled',
        header: () => h('div', { class: 'text-right' }, 'Enrolled'),
        cell: ({ row }) => {
            const date = row.getValue('enrolled_at') as string;
            return h('div', { class: 'text-right text-xs text-muted-foreground' }, dayjs(date).fromNow());
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const student = row.original;

            return h(
                'div',
                { class: 'relative' },
                h(DropdownAction, {
                    student,
                }),
            );
        },
    },
];
