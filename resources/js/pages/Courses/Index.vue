<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type CoursePagination } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { FolderOpen, Search, ArrowUpDown, Users, Copy, ArrowRight, GalleryVertical, List, Calendar } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import PaginationComponent from '@/components/Pagination.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle, } from '@/components/ui/empty'
import AddCourseButton from '@/components/AddCourseButton.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import { Tabs, TabsList, TabsTrigger, } from '@/components/ui/tabs'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { useDebounceFn } from '@vueuse/core';
import 'vue-sonner/style.css';
import Button from '@/components/ui/button/Button.vue';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

dayjs.extend(relativeTime)

const isLoading = ref(false);
const getStoredViewMode = (): string => {
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('courses-view-mode');
        return stored === 'list' || stored === 'grid' ? stored : 'grid';
    }
    return 'grid';
};
const viewMode = ref(getStoredViewMode());
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Courses',
        href: '/courses',
    },
];

const page = usePage();
const courses = computed(() => page.props.courses as CoursePagination);
const filters = computed(() => page.props.filters as { search: string; sort: string });
const searchQuery = ref(filters.value.search);
const sortBy = ref(filters.value.sort);

watch(viewMode, (newValue) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('courses-view-mode', newValue);
    }
});

const performSearch = () => {
    router.get('/courses',
        {
            search: searchQuery.value,
            sort: sortBy.value,
            page: 1
        },
        {
            preserveScroll: true,
            preserveState: true,
            onStart: () => {
                isLoading.value = true;
            },
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
};

const debouncedSearch = useDebounceFn(() => {
    performSearch();
}, 500);

watch(searchQuery, () => {
    isLoading.value = true;
    debouncedSearch();
});

watch(sortBy, () => {
    performSearch();
});

const handlePageChange = (pageNumber: number) => {
    isLoading.value = true;
    router.get('/courses',
        {
            page: pageNumber,
            search: searchQuery.value,
            sort: sortBy.value
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
};

const hasActiveFilters = computed(() => {
    return (searchQuery.value && searchQuery.value.trim() !== '') || sortBy.value !== 'newest';
});
const hasContent = computed(() => {
    return courses.value.data.length > 0 || hasActiveFilters.value;
});

function copy(text: string) {
    navigator.clipboard.writeText(text)
    toast('Copied to clipboard', {
    })
}
</script>

<template>

    <Head title="Courses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions v-if="hasContent">
            <AddCourseButton />
        </template>

        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <template v-if="isLoading || hasContent">
                <div>
                    <h1 class="text-lg sm:text-2xl font-bold tracking-tight cursor-default">Courses</h1>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-1 mb-4">
                        Manage your courses and access codes.
                    </p>

                    <div class="flex gap-2 flex-wrap items-center">
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
                            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input v-model="searchQuery" type="search" placeholder="Search" class="pl-8 w-full"
                                aria-label="Search courses" />
                        </div>
                        <Select v-model="sortBy" aria-label="Sort courses">
                            <SelectTrigger class="w-[170px]">
                                <ArrowUpDown class="h-4 w-4 mr-2" />
                                <SelectValue placeholder="Sort by" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Sort By</SelectLabel>
                                    <SelectItem value="newest">Newest First</SelectItem>
                                    <SelectItem value="oldest">Oldest First</SelectItem>
                                    <SelectItem value="name_asc">Name (A-Z)</SelectItem>
                                    <SelectItem value="name_desc">Name (Z-A)</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <template v-if="isLoading">
                    <div v-if="viewMode === 'grid'" class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="i in 6" :key="i" class="h-full">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <Skeleton class="h-6 w-3/4" />
                                        <Skeleton class="h-4 w-1/2" />
                                    </div>
                                    <Skeleton class="h-6 w-16" />
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0 pb-4">
                                <Skeleton class="h-8 w-full" />
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
                                    <TableHead class="w-[40%]">Course</TableHead>
                                    <TableHead>Access Code</TableHead>
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
                                        <Skeleton class="h-8 w-28" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-24" />
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Skeleton class="h-4 w-16 ml-auto" />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </template>

                <template v-else-if="courses.data.length > 0">
                    <div v-if="viewMode === 'grid'" class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                        <Link v-for="course in courses.data" :key="course.id" :href="`/courses/${course.id}`"
                            prefetch="mount" class="block" :aria-label="`View details for ${course.name}`">
                            <Card
                                class="group relative overflow-hidden transition-all hover:shadow-lg hover:border-primary/50 h-full">
                                <CardHeader class="pb-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <CardTitle
                                                class="text-lg font-semibold line-clamp-2 group-hover:text-primary dark:text-white transition-colors">
                                                {{ course.name }}
                                            </CardTitle>
                                            <CardDescription class="mt-1.5 flex items-center gap-1.5 text-xs">
                                                <Calendar class="h-3.5 w-3.5" aria-hidden="true" />
                                                <span>Created {{ dayjs(course.created_at).fromNow() }}</span>
                                            </CardDescription>
                                        </div>

                                        <div>
                                            <div class="flex items-center p-2 bg-muted border rounded-md">
                                                <code
                                                    class="text-sm font-mono font-semibold">{{ course.access_code }}</code>
                                            </div>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent class="pt-0 pb-4">
                                </CardContent>

                                <CardFooter class="pt-0 pb-4">
                                    <div
                                        class="flex items-center gap-2 text-sm font-medium text-primary dark:text-white group-hover:underline w-full">
                                        <span>View Details</span>
                                        <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1"
                                            aria-hidden="true" />
                                    </div>
                                </CardFooter>
                            </Card>
                        </Link>
                    </div>

                    <div v-else class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[40%]">Course</TableHead>
                                    <TableHead>Access Code</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="course in courses.data" :key="course.id"
                                    class="group cursor-pointer hover:bg-muted/50"
                                    @click="router.visit(`/courses/${course.id}`)"
                                    :aria-label="`View details for ${course.name}`" tabindex="0"
                                    @keydown.enter="router.visit(`/courses/${course.id}`)"
                                    @keydown.space.prevent="router.visit(`/courses/${course.id}`)">
                                    <TableCell>
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="text-2xs lg:text-sm group-hover:text-primary dark:text-white transition-colors line-clamp-1">
                                                {{ course.name }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <code
                                                class="text-xs lg:text-sm font-mono font-semibold">{{ course.access_code }}</code>
                                            <Button variant="outline" size="icon" @click.stop="copy(course.access_code)"
                                                aria-label="Copy access-code" class="h-7 w-7">
                                                <Copy class="size-3.5" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                            <Calendar class="h-3.5 w-3.5 hidden xl:block" aria-hidden="true" />
                                            <span>{{ dayjs(course.created_at).fromNow() }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="`/courses/${course.id}`" prefetch="mount"
                                            class="inline-flex items-center gap-1 text-sm font-medium text-primary dark:text-white group-hover:underline"
                                            @click.stop :aria-label="`View details for ${course.name}`">
                                            <span>View</span>
                                            <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1"
                                                aria-hidden="true" />
                                        </Link>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="mt-4">
                        <PaginationComponent :pagination="courses" @page-change="handlePageChange" />
                    </div>
                </template>

                <template v-else>
                    <Empty class="py-12">
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <FolderOpen class="h-12 w-12 text-muted-foreground" />
                            </EmptyMedia>
                            <EmptyTitle>No Courses Found</EmptyTitle>
                            <EmptyDescription>
                                No courses match your current filters. Try adjusting your search or filters.
                            </EmptyDescription>
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
                        <EmptyTitle>No Courses Yet</EmptyTitle>
                        <EmptyDescription>
                            You haven't created any courses yet. Get started by creating your first course.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <AddCourseButton />
                    </EmptyContent>
                </Empty>
            </template>
        </div>
    </AppLayout>

    <Toaster rich-colors />
</template>
