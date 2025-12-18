<script setup lang="ts">
import PaginationComponent from '@/components/Pagination.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Toaster } from '@/components/ui/sonner';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import StudentAppLayout from '@/layouts/StudentAppLayout.vue';
import type { BreadcrumbItem, CoursePagination } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { ArrowRight, ArrowUpDown, Calendar, GalleryVertical, GraduationCap, List, Search } from 'lucide-vue-next';
import { computed, ref, watch, watchEffect } from 'vue';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

dayjs.extend(relativeTime);

const page = usePage();

watchEffect(() => {
    if (page.props.flash?.success) {
        toast.success(page.props.flash.success as string);
    }
});

const enrolledCourses = computed(() => page.props.enrolledCourses as CoursePagination);
const filters = computed(() => page.props.filters as { search: string; sort: string });

const isLoading = ref(false);
const getStoredViewMode = (): string => {
    if (typeof window !== 'undefined') {
        const stored = localStorage.getItem('courses-view-mode');
        return stored === 'list' || stored === 'grid' ? stored : 'grid';
    }
    return 'grid';
};
const viewMode = ref(getStoredViewMode());
const searchQuery = ref(filters.value.search);
const sortBy = ref(filters.value.sort);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Courses',
        href: '/student/courses',
    },
];

watch(viewMode, (newValue) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('courses-view-mode', newValue);
    }
});

const performSearch = () => {
    router.get(
        '/student/courses',
        {
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

watch(sortBy, () => {
    performSearch();
});

const handlePageChange = (pageNumber: number) => {
    isLoading.value = true;
    router.get(
        '/student/courses',
        {
            page: pageNumber,
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
    return (searchQuery.value && searchQuery.value.trim() !== '') || sortBy.value !== 'newest';
});

const hasContent = computed(() => {
    return enrolledCourses.value.data.length > 0 || hasActiveFilters.value;
});
</script>

<template>

    <Head title="Courses" />

    <StudentAppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <template v-if="isLoading || hasContent">
                <div>
                    <h1 class="cursor-default text-lg font-bold tracking-tight sm:text-2xl">My Enrolled Courses</h1>
                    <p class="mt-1 mb-4 text-xs text-muted-foreground sm:text-sm">View and access your enrolled courses
                    </p>

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
                            <Input v-model="searchQuery" type="search" placeholder="Search courses" class="w-full pl-8"
                                aria-label="Search courses" />
                        </div>
                        <Select v-model="sortBy" aria-label="Sort courses">
                            <SelectTrigger class="w-[170px]">
                                <ArrowUpDown class="mr-2 h-4 w-4" />
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
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="i in 6" :key="i" class="h-full">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <Skeleton class="h-8 w-8 rounded-lg" />
                                            <Skeleton class="h-6 w-20" />
                                        </div>
                                        <Skeleton class="h-6 w-3/4" />
                                        <Skeleton class="h-4 w-1/2" />
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="pt-0 pb-4">
                                <div class="space-y-2">
                                    <Skeleton class="h-4 w-20" />
                                    <Skeleton class="h-4 w-32" />
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
                                    <TableHead class="w-[40%]">Course</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Instructor</TableHead>
                                    <TableHead>Enrolled</TableHead>
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
                                        <Skeleton class="h-4 w-32" />
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

                <template v-else-if="enrolledCourses.data.length > 0">
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Link v-for="course in enrolledCourses.data" :key="course.id"
                            :href="`/student/courses/${course.id}`" prefetch="mount" class="block"
                            :aria-label="`View activities for ${course.name}`">
                            <Card
                                class="group relative h-full overflow-hidden transition-all hover:border-primary/50 hover:shadow-lg">
                                <CardHeader class="pb-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <CardTitle
                                                class="line-clamp-2 text-lg font-semibold transition-colors group-hover:text-primary dark:text-white">
                                                {{ course.name }}
                                            </CardTitle>
                                            <CardDescription class="mt-1.5 flex items-center gap-1.5 text-xs">
                                                <Calendar class="h-3.5 w-3.5" aria-hidden="true" />
                                                <span>Enrolled {{ dayjs(course.enrolled_at).fromNow() }}</span>
                                            </CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent class="pt-0 pb-4">
                                    <div class="space-y-2">
                                        <div class="text-sm text-muted-foreground">Instructor</div>
                                        <div class="text-sm font-medium">
                                            {{ course.user?.name || 'Unknown' }}
                                        </div>
                                    </div>
                                </CardContent>

                                <CardFooter class="pt-0 pb-4">
                                    <div
                                        class="flex w-full items-center gap-2 text-sm font-medium text-primary group-hover:underline dark:text-white">
                                        <span>View Course</span>
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
                                    <TableHead>Instructor</TableHead>
                                    <TableHead>Enrolled</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="course in enrolledCourses.data" :key="course.id"
                                    class="group cursor-pointer hover:bg-muted/50"
                                    @click="router.visit(`/student/courses/${course.id}`)"
                                    :aria-label="`View activities for ${course.name}`" tabindex="0"
                                    @keydown.enter="router.visit(`/student/courses/${course.id}`)"
                                    @keydown.space.prevent="router.visit(`/student/courses/${course.id}`)">
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="line-clamp-1 text-2xs font-medium transition-colors group-hover:text-primary lg:text-sm dark:text-white">
                                                {{ course.name }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm">{{ course.user?.name || 'Unknown' }}</span>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                            <Calendar class="hidden h-3.5 w-3.5 xl:block" aria-hidden="true" />
                                            <span>{{ dayjs(course.enrolled_at).fromNow() }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="`/student/courses/${course.id}`" prefetch="mount"
                                            class="inline-flex items-center gap-1 text-sm font-medium text-primary group-hover:underline dark:text-white"
                                            @click.stop :aria-label="`View activities for ${course.name}`">
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
                        <PaginationComponent :pagination="enrolledCourses" @page-change="handlePageChange" />
                    </div>
                </template>

                <template v-else>
                    <Empty class="py-12">
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <GraduationCap class="h-12 w-12 text-muted-foreground" />
                            </EmptyMedia>
                            <EmptyTitle>No Courses Found</EmptyTitle>
                            <EmptyDescription> No courses match your current filters. </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                </template>
            </template>

            <template v-else>
                <Empty class="py-12">
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <GraduationCap class="h-12 w-12 text-muted-foreground" />
                        </EmptyMedia>
                        <EmptyTitle>No Enrolled Courses Yet</EmptyTitle>
                        <EmptyDescription>
                            You haven't enrolled in any courses yet. Enroll using an access code provided by your
                            Professor.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent> </EmptyContent>
                </Empty>
            </template>
        </div>
    </StudentAppLayout>

    <Toaster rich-colors />
</template>
