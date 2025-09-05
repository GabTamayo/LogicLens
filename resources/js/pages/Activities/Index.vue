<script setup lang="ts">
import { ModalLink } from '@inertiaui/modal-vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table';
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { Badge } from '@/components/ui/badge';
import { Plus } from 'lucide-vue-next';
import { Button } from "@/components/ui/button"
import type { Activity } from '@/types';

dayjs.extend(relativeTime)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: '/activities',
    },
];

interface Props {
    activities: Activity[];
}

defineProps<Props>();
</script>

<template>

    <Head title="Activities" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <ModalLink href="/activities/create">
                    <Button>
                        <Plus /> Add Activity
                    </Button>
                </ModalLink>
            </div>

            <Table>
                <TableCaption>A list of your recent invoices.</TableCaption>
                <TableBody>
                    <TableRow v-for="activity in activities" :key="activity.id" class="h-25">
                        <TableCell>
                            <div class="flex flex-col space-y-2">
                                <p class="font-medium">{{ activity.title }}</p>
                                <div class="space-x-4 flex items-center">
                                    <div class="space-x-2">
                                        <Badge variant="outline">1 Active</Badge>
                                        <Badge variant="secondary">1 Closed</Badge>
                                        <Badge>1 Expired</Badge>
                                    </div>
                                    <span class="text-gray-600 text-xs">Activity {{ dayjs(activity.created_at).fromNow()
                                    }}</span>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell class="text-right">
                            <a href="#" class="text-gray-600 hover:underline text-sm">View Details</a>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
