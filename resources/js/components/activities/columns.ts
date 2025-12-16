import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { Badge } from '@/components/ui/badge'
import { Circle } from 'lucide-vue-next'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import DropdownAction from './data-table.dropdown.vue'
import { useLanguage } from '@/composables/useLanguage'

dayjs.extend(relativeTime)
const { getLanguageConfig } = useLanguage()

export interface ActivityRow {
    id: string
    activity_id: string
    activity_title: string
    activity_language: string
    token: string
    is_open: boolean
    expires_at: string | null
    created_at: string
    submissions_count: number
}

export const columns: ColumnDef<ActivityRow>[] = [
    {
        accessorKey: 'activity_title',
        label: 'Activity',
        header: () => h('div', { class: '' }, 'Activity'),
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('activity_title')),
    },
    {
        accessorKey: 'activity_language',
        label: 'Language',
        header: () => h('div', {}, 'Language'),
        cell: ({ row }) => {
            const language = row.getValue('activity_language') as string
            const { colors, logo } = getLanguageConfig(language)

            return h(
                Badge,
                {
                    variant: 'outline',
                    class: `flex items-center gap-1.5 ${colors}`,
                },
                () => [
                    logo
                        ? h('img', {
                            src: logo,
                            alt: language,
                            class: 'h-3.5 w-3.5 object-contain',
                        })
                        : null,
                    h('span', { class: 'text-xs font-medium' }, language),
                ]
            )
        },
    },
    {
        accessorKey: 'is_open',
        label: 'Status',
        header: () => h('div', { class: '' }, 'Status'),
        cell: ({ row }) => {
            const isOpen = row.getValue('is_open')
            return h('div', { class: 'flex items-center gap-1.5' }, [
                h(Circle, {
                    class: `h-2 w-2 fill-current ${isOpen ? 'text-green-500' : 'text-red-500'}`
                }),
                h('span', { class: 'text-sm' }, isOpen ? 'Open' : 'Closed')
            ])
        },
    },
    {
        accessorKey: 'submissions_count',
        label: 'Submissions',
        header: () => h('div', {}, 'Submissions'),
        cell: ({ row }) =>
            h(
                Badge,
                {
                    class:
                        'h-[25px] w-[30px] overflow-hidden text-ellipsis rounded-full flex items-center justify-center',
                },
                () =>
                    h(
                        'span',
                        { class: 'font-mono font-semibold text-xs' },
                        String(row.getValue('submissions_count'))
                    )
            ),
    },
    {
        accessorKey: 'created_at',
        label: 'Assigned',
        header: () => h('div', { class: 'text-right' }, 'Assigned'),
        cell: ({ row }) => {
            const date = row.getValue('created_at') as string
            return h('div', { class: 'text-right text-xs text-muted-foreground' }, dayjs(date).fromNow())
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const activity = row.original

            return h('div', { class: 'relative' }, h(DropdownAction, {
                activity,
            }))
        },
    }
]
