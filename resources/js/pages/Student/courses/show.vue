<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PaginationComponent from '@/components/Pagination.vue';
import { columns as studentColumns } from '@/components/students(student)/columns';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Item, ItemActions, ItemContent, ItemDescription, ItemTitle } from '@/components/ui/item';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useLanguage } from '@/composables/useLanguage';
import { useScore } from '@/composables/useScore';
import StudentAppLayout from '@/layouts/StudentAppLayout.vue';
import type { BreadcrumbItem, StudentCourseShowProps } from '@/types';
import { Deferred, Head, Link, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Calendar, CalendarCheck, Eye, LoaderCircle, Play } from 'lucide-vue-next';
import { ref, watch } from 'vue';

dayjs.extend(relativeTime);

const { getLanguageLogo, getLanguageColor } = useLanguage();
const { getScoreDisplay, getScoreBackgroundClass } = useScore();

const props = defineProps<StudentCourseShowProps>();

const isInitialLoadDone = ref(false);
const activeTab = ref(props.activeTab || 'activities');
const activitiesPage = ref(1);
const completedPage = ref(1);
const studentsPage = ref(1);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Courses',
        href: '/student/courses',
    },
    {
        title: props.course.name,
        href: `/student/courses/${props.course.id}`,
    },
];

watch(activeTab, (newTab) => {
    const page = newTab === 'students' ? studentsPage.value : newTab === 'completed' ? completedPage.value : activitiesPage.value;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { tab: newTab, page },
        preserveScroll: true,
        preserveState: true,
        only:
            newTab === 'students'
                ? ['students', 'activeTab']
                : newTab === 'completed'
                  ? ['completedActivities', 'activeTab']
                  : ['activities', 'activeTab'],
    });
});

const handleActivitiesPageChange = (page: number) => {
    activitiesPage.value = page;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { page, tab: 'activities' },
        preserveScroll: true,
        preserveState: true,
        only: ['activities'],
    });
};

const handleCompletedPageChange = (page: number) => {
    completedPage.value = page;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { page, tab: 'completed' },
        preserveScroll: true,
        preserveState: true,
        only: ['completedActivities'],
    });
};

const handleStudentsPageChange = (page: number) => {
    studentsPage.value = page;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { page, tab: 'students' },
        preserveScroll: true,
        preserveState: true,
        only: ['students'],
    });
};
</script>

<template>
    <Head :title="`${course.name}`" />

    <StudentAppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <!-- Header Card -->
            <Card>
                <CardHeader>
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-wrap items-center">
                                <CardTitle class="text-2xl">{{ course.name }}</CardTitle>
                            </div>
                            <CardDescription class="flex flex-wrap items-center gap-2">
                                <span>Created by {{ course.user?.name }}</span>
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Tabs Section -->
            <Card>
                <CardContent class="p-0">
                    <Tabs v-model="activeTab" class="w-full">
                        <div class="border-b px-6">
                            <TabsList class="h-auto rounded-none border-b-0 bg-transparent p-0">
                                <TabsTrigger
                                    value="activities"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pt-2 pb-3 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                                >
                                    Activities
                                </TabsTrigger>
                                <TabsTrigger
                                    value="completed"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pt-2 pb-3 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                                >
                                    Completed
                                </TabsTrigger>
                                <TabsTrigger
                                    value="students"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pt-2 pb-3 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                                >
                                    Students
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <TabsContent value="activities" class="m-0 p-6">
                            <template v-if="activities">
                                <Deferred data="activities" @resolve="isInitialLoadDone = true">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading activities...</span>
                                        </div>
                                    </template>
                                    <div class="space-y-4">
                                        <div v-if="activities?.data.length" class="space-y-4">
                                            <Item variant="outline" v-for="activity in activities.data" :key="activity.id">
                                                <ItemContent>
                                                    <ItemTitle class="text-xl font-bold capitalize">{{ activity.activity_title }}</ItemTitle>
                                                    <ItemDescription>
                                                        <span
                                                            :class="getLanguageColor(activity.activity_language)"
                                                            class="mb-2 inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-medium"
                                                        >
                                                            <img
                                                                v-if="getLanguageLogo(activity.activity_language)"
                                                                :src="getLanguageLogo(activity.activity_language)!"
                                                                :alt="activity.activity_language"
                                                                class="h-4 w-4 object-contain"
                                                            />
                                                            {{ activity.activity_language }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5">
                                                            <Calendar class="h-3.5 w-3.5 text-green-600 dark:text-green-400" />
                                                            {{ dayjs(activity.created_at).format('MMM D, YYYY h:mm A') }}
                                                        </span>
                                                        <span v-if="activity.expires_at" class="flex items-center gap-1.5">
                                                            <Calendar class="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                                            {{ dayjs(activity.expires_at).format('MMM D, YYYY h:mm A') }}
                                                        </span>
                                                        <span v-else class="flex items-center gap-1.5 text-muted-foreground">
                                                            <Calendar class="h-3.5 w-3.5" />
                                                            No deadline
                                                        </span>
                                                    </ItemDescription>
                                                </ItemContent>
                                                <ItemActions>
                                                    <TooltipProvider>
                                                        <Tooltip>
                                                            <TooltipTrigger as-child>
                                                                <Link :href="`/student/submit/${activity.token}`">
                                                                    <Button size="icon-lg" class="rounded-full">
                                                                        <Play class="size-5 fill-white stroke-none" />
                                                                    </Button>
                                                                </Link>
                                                            </TooltipTrigger>
                                                            <TooltipContent> Start </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                </ItemActions>
                                            </Item>
                                        </div>
                                        <div v-else class="py-8 text-center text-muted-foreground">No activities found</div>
                                        <PaginationComponent
                                            v-if="activities && activities.data.length > 0"
                                            :pagination="activities"
                                            @page-change="handleActivitiesPageChange"
                                        />
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading activities...</span>
                            </div>
                        </TabsContent>

                        <TabsContent value="completed" class="m-0 p-6">
                            <template v-if="completedActivities">
                                <Deferred data="completedActivities">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading completed activities...</span>
                                        </div>
                                    </template>
                                    <div class="space-y-4">
                                        <div v-if="completedActivities?.data.length" class="space-y-4">
                                            <Item
                                                variant="outline"
                                                v-for="activity in completedActivities.data"
                                                :key="activity.id"
                                                :class="[
                                                    activity.submitted_at
                                                        ? getScoreBackgroundClass(activity.score, activity.total_score)
                                                        : 'bg-slate-500 dark:bg-slate-700',
                                                    !activity.is_open ? 'relative' : ''
                                                ]"
                                                class="shadow-md transition-shadow duration-200 hover:shadow-lg"
                                            >
                                                <div v-if="!activity.is_open" class="absolute inset-0 bg-slate-900/40 dark:bg-slate-950/50 rounded-lg pointer-events-none"></div>
                                                <ItemContent :class="!activity.is_open ? 'relative z-10 opacity-80' : ''">
                                                    <ItemTitle class="text-xl font-bold text-white capitalize">
                                                        {{ activity.activity_title }}
                                                    </ItemTitle>
                                                    <ItemDescription>
                                                        <span
                                                            class="mb-2 inline-flex items-center gap-1.5 rounded-md border border-white/30 bg-white/20 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-sm"
                                                        >
                                                            <img
                                                                v-if="getLanguageLogo(activity.activity_language)"
                                                                :src="getLanguageLogo(activity.activity_language)!"
                                                                :alt="activity.activity_language"
                                                                class="h-4 w-4 object-contain"
                                                            />
                                                            {{ activity.activity_language }}
                                                        </span>

                                                        <div class="flex items-center gap-3">
                                                            <span
                                                                v-if="!activity.is_open"
                                                                class="text-xs sm:text-md flex items-center gap-1.5 font-semibold text-white/90"
                                                            >
                                                                <CalendarCheck class="h-3.5 w-3.5" />
                                                                Closed
                                                            </span>
                                                            <div class="hidden md:block">
                                                            <span v-if="activity.submitted_at" class="text-xs sm:text-md flex items-center gap-1.5 text-white/90">
                                                                <Calendar class="h-3.5 w-3.5" />
                                                                Submitted: {{ dayjs(activity.submitted_at).format('MMM D, YYYY h:mm A') }}
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </ItemDescription>
                                                </ItemContent>
                                                <ItemActions :class="!activity.is_open ? 'relative z-10 opacity-80 flex items-center gap-4' : 'flex items-center gap-4'">
                                                    <div class="flex flex-col items-end gap-1">
                                                        <span class="text-xs font-medium tracking-wider text-white/70 uppercase">Score</span>
                                                        <span v-if="activity.submitted_at" class="text-lg sm:text-2xl font-bold text-white">
                                                            {{ getScoreDisplay(activity.score, activity.total_score).text }}
                                                        </span>
                                                        <span v-else class="text-sm font-semibold italic text-white/70">
                                                            Not Submitted
                                                        </span>
                                                    </div>
                                                    <TooltipProvider>
                                                        <Tooltip>
                                                            <TooltipTrigger as-child>
                                                                <div>
                                                                    <Link v-if="activity.is_open" :href="`/student/submit/${activity.token}`">
                                                                        <Button
                                                                            size="icon-lg"
                                                                            variant="secondary"
                                                                            class="rounded-full border-white/30 bg-white/20 text-white backdrop-blur-sm hover:bg-white/30"
                                                                        >
                                                                            <Eye class="size-5" />
                                                                        </Button>
                                                                    </Link>
                                                                    <Button
                                                                        v-else
                                                                        size="icon-lg"
                                                                        variant="secondary"
                                                                        disabled
                                                                        class="cursor-not-allowed rounded-full border-white/20 bg-white/10 text-white/50 backdrop-blur-sm"
                                                                    >
                                                                        <Eye class="size-5" />
                                                                    </Button>
                                                                </div>
                                                            </TooltipTrigger>
                                                            <TooltipContent>
                                                                {{ activity.is_open ? 'View Submission' : 'Activity Closed' }}
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                </ItemActions>
                                            </Item>
                                        </div>
                                        <div v-else class="py-8 text-center text-muted-foreground">No completed activities found</div>
                                        <PaginationComponent
                                            v-if="completedActivities && completedActivities.data.length > 0"
                                            :pagination="completedActivities"
                                            @page-change="handleCompletedPageChange"
                                        />
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading completed activities...</span>
                            </div>
                        </TabsContent>

                        <TabsContent value="students" class="m-0 p-6">
                            <template v-if="students">
                                <Deferred data="students">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading students...</span>
                                        </div>
                                    </template>
                                    <DataTable
                                        :columns="studentColumns"
                                        :data="students.data"
                                        :pagination="students as any"
                                        @page-change="handleStudentsPageChange"
                                    />
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading students...</span>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </StudentAppLayout>
</template>
