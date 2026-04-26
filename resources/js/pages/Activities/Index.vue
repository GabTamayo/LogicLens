<script setup lang="ts">
import AddActivityButton from '@/components/AddActivityButton.vue';
import PaginationComponent from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Toaster } from '@/components/ui/sonner';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useLanguage } from '@/composables/useLanguage';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ActivityPagination } from '@/types';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { ArrowRight, ArrowUpDown, Calendar, Circle, Code2, FolderOpen, GalleryVertical, List, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import 'vue-sonner/style.css';

dayjs.extend(relativeTime);

const { getLanguageColor, getLanguageLogo } = useLanguage();

const isLoading = ref(false);
const getStoredViewMode = (): string => {
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('activities-view-mode');
        return stored === 'list' || stored === 'grid' ? stored : 'grid';
    }
    return 'grid';
};
const viewMode = ref(getStoredViewMode());
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: '/activities',
    },
];
const page = usePage();
const activities = computed(() => page.props.activities as ActivityPagination);
const filters = computed(() => page.props.filters as { language: string; search: string; sort: string });
const selectedLanguage = ref(filters.value.language);
const searchQuery = ref(filters.value.search);
const sortBy = ref(filters.value.sort);

watch(viewMode, (newValue) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('activities-view-mode', newValue);
    }
});

const performSearch = () => {
    router.get(
        '/activities',
        {
            language: selectedLanguage.value,
            search: searchQuery.value,
            sort: sortBy.value,
            page: 1,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onStart: () => {
                isLoading.value = true;
            },
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const debouncedSearch = useDebounceFn(() => {
    performSearch();
}, 500);

watch(searchQuery, () => {
    isLoading.value = true;
    debouncedSearch();
});

watch(selectedLanguage, () => {
    performSearch();
});

watch(sortBy, () => {
    performSearch();
});

const handlePageChange = (pageNumber: number) => {
    isLoading.value = true;
    router.get(
        '/activities',
        {
            page: pageNumber,
            language: selectedLanguage.value,
            search: searchQuery.value,
            sort: sortBy.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const hasActiveFilters = computed(() => {
    return selectedLanguage.value !== 'all' || (searchQuery.value && searchQuery.value.trim() !== '') || sortBy.value !== 'newest';
});

const hasContent = computed(() => {
    return activities.value.data.length > 0 || hasActiveFilters.value;
});
</script>

<template>
    <Head title="Activities" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions v-if="hasContent">
            <AddActivityButton />
        </template>

        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <template v-if="isLoading || hasContent">
                <div>
                    <h1 class="cursor-default text-lg font-bold tracking-tight sm:text-2xl">Activities</h1>
                    <p class="mt-1 mb-4 text-xs text-muted-foreground sm:text-sm">Organize your activities and assign them to your courses.</p>

                    <div class="flex flex-wrap items-center gap-2">
                        <Tabs v-model="viewMode">
                            <TabsList class="w-fit">
                                <TabsTrigger value="grid" aria-label="Grid view">
                                    <GalleryVertical class="size-4" />
                                </TabsTrigger>
                                <TabsTrigger value="list" aria-label="List view">
                                    <List class="size-4" />
                                </TabsTrigger>
                            </TabsList>
                        </Tabs>
                        <div class="relative w-[180px] sm:w-[280px]">
                            <Search class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" type="search" placeholder="Search" class="w-full pl-8" aria-label="Search activities" />
                        </div>
                        <Select v-model="selectedLanguage" aria-label="Filter by language">
                            <SelectTrigger class="w-[100px]">
                                <SelectValue placeholder="Filter Language" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Language</SelectLabel>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem value="java">Java</SelectItem>
                                    <SelectItem value="python">Python</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <Select v-model="sortBy" aria-label="Sort activities">
                            <SelectTrigger class="w-[170px]">
                                <ArrowUpDown class="mr-2 h-4 w-4" />
                                <SelectValue placeholder="Sort by" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Sort By</SelectLabel>
                                    <SelectItem value="newest">Newest First</SelectItem>
                                    <SelectItem value="oldest">Oldest First</SelectItem>
                                    <SelectItem value="title_asc">Title (A-Z)</SelectItem>
                                    <SelectItem value="title_desc">Title (Z-A)</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <template v-if="isLoading">
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="i in 6" :key="i" class="h-full">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <Skeleton class="h-6 w-3/4" />
                                        <Skeleton class="h-4 w-1/2" />
                                    </div>
                                    <Skeleton class="h-8 w-20" />
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0 pb-4">
                                <div class="flex items-center gap-4">
                                    <Skeleton class="h-8 w-24" />
                                    <Skeleton class="h-8 w-24" />
                                </div>
                            </CardContent>
                            <CardFooter class="pt-0 pb-4">
                                <Skeleton class="h-4 w-28" />
                            </CardFooter>
                        </Card>
                    </div>

                    <div v-else class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[40%]">Activity</TableHead>
                                    <TableHead>Language</TableHead>
                                    <TableHead>Active Links</TableHead>
                                    <TableHead>Closed Links</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="i in 6" :key="i">
                                    <TableCell>
                                        <Skeleton class="h-4 w-3/4" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-6 w-20" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-8" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-8" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-24" />
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Skeleton class="ml-auto h-4 w-16" />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </template>

                <template v-else-if="activities.data.length > 0">
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="activity in activities.data"
                            :key="activity.id"
                            :href="`/activities/${activity.id}`"
                            prefetch="mount"
                            class="block"
                            :aria-label="`View details for ${activity.title}`"
                        >
                            <Card class="group relative h-full overflow-hidden transition-all hover:border-primary/50 hover:shadow-lg">
                                <CardHeader class="pb-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <CardTitle
                                                class="line-clamp-2 text-lg font-semibold transition-colors group-hover:text-primary dark:text-white"
                                            >
                                                {{ activity.title }}
                                            </CardTitle>
                                            <CardDescription class="mt-1.5 flex items-center gap-1.5 text-xs">
                                                <Calendar class="h-3.5 w-3.5" aria-hidden="true" />
                                                <span>Created {{ dayjs(activity.created_at).fromNow() }}</span>
                                            </CardDescription>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div
                                                :class="[
                                                    'rounded-md border px-2.5 py-1 text-xs font-medium',
                                                    getLanguageColor(activity.language_text),
                                                ]"
                                            >
                                                <div class="flex items-center gap-1.5">
                                                    <img
                                                        v-if="getLanguageLogo(activity.language_text)"
                                                        :src="getLanguageLogo(activity.language_text)"
                                                        :alt="`${activity.language_text} logo`"
                                                        class="h-5 w-5 object-contain"
                                                    />
                                                    <Code2 v-else class="h-5 w-5" aria-hidden="true" />
                                                    <span>{{ activity.language_text }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent class="pt-0 pb-4">
                                    <div class="flex items-center gap-4">
                                        <Badge variant="outline">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center gap-1.5">
                                                    <Circle class="h-3 w-3 fill-current text-green-500" aria-hidden="true" />
                                                    <span class="text-sm font-medium">{{ activity.open_links_count }}</span>
                                                </div>
                                                <span class="text-xs text-muted-foreground">Active</span>
                                            </div>
                                        </Badge>

                                        <Badge variant="outline">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center gap-1.5">
                                                    <Circle class="h-3 w-3 fill-current text-red-500" aria-hidden="true" />
                                                    <span class="text-sm font-medium">{{ activity.closed_links_count }}</span>
                                                </div>
                                                <span class="text-xs text-muted-foreground">Closed</span>
                                            </div>
                                        </Badge>
                                    </div>
                                </CardContent>

                                <CardFooter class="pt-0 pb-4">
                                    <div
                                        class="flex w-full items-center gap-2 text-sm font-medium text-primary group-hover:underline dark:text-white"
                                    >
                                        <span>View Details</span>
                                        <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                                    </div>
                                </CardFooter>
                            </Card>
                        </Link>
                    </div>

                    <div v-else class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[40%]">Activity</TableHead>
                                    <TableHead>Language</TableHead>
                                    <TableHead>Active Links</TableHead>
                                    <TableHead>Closed Links</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="activity in activities.data"
                                    :key="activity.id"
                                    class="group cursor-pointer hover:bg-muted/50"
                                    @click="router.visit(`/activities/${activity.id}`)"
                                    :aria-label="`View details for ${activity.title}`"
                                    tabindex="0"
                                    @keydown.enter="router.visit(`/activities/${activity.id}`)"
                                    @keydown.space.prevent="router.visit(`/activities/${activity.id}`)"
                                >
                                    <TableCell>
                                        <div class="flex flex-col gap-1">
                                            <span class="line-clamp-1 text-2xs transition-colors group-hover:text-primary lg:text-sm dark:text-white">
                                                {{ activity.title }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div
                                            :class="[
                                                'inline-flex w-fit items-center gap-0.5 rounded-md border px-2 py-1 text-2xs font-medium lg:text-xs',
                                                getLanguageColor(activity.language_text),
                                            ]"
                                        >
                                            <img
                                                v-if="getLanguageLogo(activity.language_text)"
                                                :src="getLanguageLogo(activity.language_text)"
                                                :alt="`${activity.language_text} logo`"
                                                class="h-4 w-4 object-contain"
                                            />
                                            <Code2 v-else class="h-4 w-4" aria-hidden="true" />
                                            <span>{{ activity.language_text }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1.5">
                                            <Circle class="h-2 w-2 fill-current text-green-500" aria-hidden="true" />
                                            <span class="text-sm font-medium">{{ activity.open_links_count }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1.5">
                                            <Circle class="h-2 w-2 fill-current text-red-500" aria-hidden="true" />
                                            <span class="text-sm font-medium">{{ activity.closed_links_count }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                            <Calendar class="hidden h-3.5 w-3.5 xl:block" aria-hidden="true" />
                                            <span>{{ dayjs(activity.created_at).fromNow() }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link
                                            :href="`/activities/${activity.id}`"
                                            prefetch="mount"
                                            class="inline-flex items-center gap-1 text-sm font-medium text-primary group-hover:underline dark:text-white"
                                            @click.stop
                                            :aria-label="`View details for ${activity.title}`"
                                        >
                                            <span>View</span>
                                            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="mt-4">
                        <PaginationComponent :pagination="activities" @page-change="handlePageChange" />
                    </div>
                </template>

                <template v-else>
                    <Empty class="py-12">
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <FolderOpen class="h-12 w-12 text-muted-foreground" />
                            </EmptyMedia>
                            <EmptyTitle>No Activities Found</EmptyTitle>
                            <EmptyDescription> No activities match your current filters. </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                </template>
            </template>

            <template v-else>
                <Empty class="py-12">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <FolderOpen class="h-12 w-12 text-muted-foreground" />
                        </EmptyMedia>
                        <EmptyTitle>No Activities Yet</EmptyTitle>
                        <EmptyDescription> You haven't created any activities yet. Get started by creating your first activity. </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <AddActivityButton />
                    </EmptyContent>
                </Empty>
            </template>
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
