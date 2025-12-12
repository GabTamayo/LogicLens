<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type CoursePagination } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { FolderOpen, Search, ArrowUpDown, Users, Copy, ArrowRight } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import PaginationComponent from '@/components/Pagination.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle, } from '@/components/ui/empty'
import AddCourseButton from '@/components/AddCourseButton.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
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
    toast('Link copied to clipboard', {
        description: 'The submission link has been copied.',
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
                        <div class="relative w-[180px] sm:w-[280px]">
                            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input v-model="searchQuery" type="search" placeholder="Search"
                                class="pl-8 w-full" aria-label="Search courses" />
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
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
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
                </template>

                <template v-else-if="courses.data.length > 0">
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="course in courses.data" :key="course.id"
                            class="group relative overflow-hidden transition-all hover:shadow-lg hover:border-primary/50 h-full">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <CardTitle
                                            class="text-lg font-semibold line-clamp-2 group-hover:text-primary dark:text-white transition-colors">
                                            {{ course.name }}
                                        </CardTitle>
                                        <CardDescription class="mt-1.5 flex items-center gap-1.5 text-xs">
                                            <span>Created {{ dayjs(course.created_at).fromNow() }}</span>
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent class="pt-0 pb-4">
                                <div class="space-y-2">
                                    <div class="text-sm text-muted-foreground">Access Code</div>
                                    <div class="flex items-center gap-2 px-3 py-2 bg-muted rounded-md">
                                        <code class="text-sm font-mono font-semibold">{{ course.access_code }}</code>
                                        <Button variant="outline" size="icon" @click="copy(course.access_code)"
                                            aria-label="Copy access-code">
                                            <Copy class="size-4" />
                                        </Button>
                                    </div>
                                </div>
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
