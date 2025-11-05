<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Table, TableBody, TableCaption, TableCell, TableRow, } from '@/components/ui/table';
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { Badge } from '@/components/ui/badge';
import { Disc, FolderOpen } from 'lucide-vue-next';
import { Button } from "@/components/ui/button"
import type { ActivityPagination } from '@/types'
import { computed, ref } from 'vue';
import PaginationComponent from '@/components/Pagination.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle, } from '@/components/ui/empty'
import AddActivityButton from '@/components/AddActivityButton.vue';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';

dayjs.extend(relativeTime)
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: '/activities',
    },
];
const page = usePage();
const activities = computed(() => page.props.activities as ActivityPagination);

const handlePageChange = (pageNumber: number) => {
    router.get('/activities', { page: pageNumber }, { preserveScroll: true });
};
</script>

<template>

    <Head title="Activities" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div v-if="activities.data.length">
                <AddActivityButton />
            </div>

            <template v-if="activities.data.length">
                <Table>
                    <TableCaption>A list of your recent activities.</TableCaption>
                    <TableBody>
                        <TableRow v-for="activity in activities.data" :key="activity.id" class="h-25">
                            <TableCell>
                                <div class="flex flex-col space-y-2">
                                    <p class="font-medium">{{ activity.title }}</p>
                                    <div class="space-x-4 flex items-center">
                                        <div class="space-x-2">
                                            <Badge variant="outline">
                                                <Disc class="w-3 h-3 text-green-600" /> {{ activity.open_links_count }}
                                                Active
                                            </Badge>
                                            <Badge variant="outline">
                                                <Disc class="w-3 h-3 text-red-600" /> {{ activity.closed_links_count }}
                                                Closed
                                            </Badge>
                                        </div>
                                        <p v-if="activity.open_links_count + activity.closed_links_count > 0"
                                            class="text-xs text-muted-foreground font-light">
                                            {{ activity.open_links_count + activity.closed_links_count }} Total Activity
                                            Links
                                        </p>
                                        <p v-else class="text-xs text-muted-foreground italic">
                                            No activity links yet
                                        </p>
                                        <span class="ms-8 text-xs text-muted-foreground font-light">
                                            Activity {{ dayjs(activity.created_at).fromNow() }}
                                        </span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <Link :href="`/activities/${activity.id}`"
                                    class="text-gray-600 hover:underline text-sm">
                                View Details
                                </Link>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </template>
            <template v-else>
                <Empty>
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <FolderOpen />
                        </EmptyMedia>
                        <EmptyTitle>No Activities Yet</EmptyTitle>
                        <EmptyDescription>
                            You haven't created any activities yet. Get started by creating your first activity.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <AddActivityButton />
                    </EmptyContent>
                </Empty>
            </template>
            <PaginationComponent :pagination="activities" @page-change="handlePageChange" />
        </div>
    </AppLayout>
    <Toaster rich-colors/>
</template>
