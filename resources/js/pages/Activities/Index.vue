<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { Circle, FolderOpen, ArrowRight, Code2, Calendar, Loader } from 'lucide-vue-next';
import type { ActivityPagination } from '@/types'
import { computed, ref, watch } from 'vue';
import PaginationComponent from '@/components/Pagination.vue';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle, } from '@/components/ui/empty'
import AddActivityButton from '@/components/AddActivityButton.vue';
import { Toaster } from '@/components/ui/sonner';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import 'vue-sonner/style.css';

dayjs.extend(relativeTime)

const isLoading = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: '/activities',
    },
];

const page = usePage();
const activities = computed(() => page.props.activities as ActivityPagination);
const filters = computed(() => page.props.filters as { language: string });

const selectedLanguage = ref(filters.value.language);

// Watch for changes in the selected language and update the URL
watch(selectedLanguage, (newValue) => {
    isLoading.value = true;
    router.get('/activities',
        { language: newValue, page: 1 },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
});

const handlePageChange = (pageNumber: number) => {
    isLoading.value = true;
    router.get('/activities',
        { page: pageNumber, language: selectedLanguage.value },
        {
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
};

const getLanguageColor = (language: string) => {
    switch (language) {
        case 'Java':
            return 'bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 border-red-200 dark:border-red-800';
        case 'Python':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800';
        default:
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border-blue-200 dark:border-blue-800';
    }
};

const getLanguageLogo = (language: string) => {
    switch (language) {
        case 'Java':
            return '/images/java-logo-png.png';
        case 'Python':
            return '/images/python-logo-png.png';
        default:
            return null;
    }
};
</script>

<template>

    <Head title="Activities" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <!-- Loading State -->
            <template v-if="isLoading">
                <div class="h-full flex items-center justify-center py-12">
                    <Loader class="h-8 w-8 animate-spin text-muted-foreground" />
                </div>
            </template>

            <template v-else-if="activities.data.length > 0 || selectedLanguage !== 'all'">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg sm:text-2xl font-bold tracking-tight">Activities</h1>
                        <p class="text-xs sm:text-sm text-muted-foreground mt-1">
                            Manage and group your submission links into activities.
                        </p>
                    </div>

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <Select v-model="selectedLanguage">
                            <SelectTrigger class="w-[130px] sm:w-[150px]">
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

                        <AddActivityButton />
                    </div>

                </div>

                <!-- Activities Grid or Empty State for Filtered Results -->
                <template v-if="activities.data.length > 0">
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                        <Link v-for="activity in activities.data" :key="activity.id"
                            :href="`/activities/${activity.id}`" prefetch="mount" class="block">
                        <Card
                            class="group relative overflow-hidden transition-all hover:shadow-lg hover:border-primary/50 h-full">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <CardTitle
                                            class="text-lg font-semibold line-clamp-2 group-hover:text-primary dark:text-white transition-colors">
                                            {{ activity.title }}
                                        </CardTitle>
                                        <CardDescription class="mt-1.5 flex items-center gap-1.5 text-xs">
                                            <Calendar class="h-3.5 w-3.5" />
                                            <span>Created {{ dayjs(activity.created_at).fromNow() }}</span>
                                        </CardDescription>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div :class="[
                                            'px-2.5 py-1 rounded-md border text-xs font-medium',
                                            getLanguageColor(activity.language_text)
                                        ]">
                                            <div class="flex items-center gap-1.5">
                                                <img v-if="getLanguageLogo(activity.language_text)"
                                                    :src="getLanguageLogo(activity.language_text)"
                                                    :alt="`${activity.language_text} logo`"
                                                    class="h-5 w-5 object-contain" />
                                                <Code2 v-else class="h-5 w-5" />
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
                                                <Circle class="h-3 w-3 text-green-500 fill-current" />
                                                <span class="text-sm font-medium">{{ activity.open_links_count }}</span>
                                            </div>
                                            <span class="text-xs text-muted-foreground">Active</span>
                                        </div>
                                    </Badge>

                                    <Badge variant="outline">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1.5">
                                                <Circle class="h-3 w-3 text-red-500 fill-current" />
                                                <span class="text-sm font-medium">{{ activity.closed_links_count
                                                }}</span>
                                            </div>
                                            <span class="text-xs text-muted-foreground">Closed</span>
                                        </div>
                                    </Badge>
                                </div>
                            </CardContent>

                            <CardFooter class="pt-0 pb-4">
                                <div
                                    class="flex items-center gap-2 text-sm font-medium text-primary dark:text-white group-hover:underline w-full">
                                    <span>View Details</span>
                                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </div>
                            </CardFooter>
                        </Card>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <PaginationComponent :pagination="activities" @page-change="handlePageChange" />
                    </div>
                </template>

                <!-- Empty State for Filtered Results -->
                <template v-else>
                    <Empty class="py-12">
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <FolderOpen class="h-12 w-12 text-muted-foreground" />
                            </EmptyMedia>
                            <EmptyTitle>No Activities Found</EmptyTitle>
                            <EmptyDescription>
                                No activities available for the selected language.
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
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
