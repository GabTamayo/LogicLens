<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { columns as studentColumns } from '@/components/students(student)/columns';
import AspectRatio from '@/components/ui/aspect-ratio/AspectRatio.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Item, ItemActions, ItemContent, ItemDescription, ItemTitle } from '@/components/ui/item';
import Separator from '@/components/ui/separator/Separator.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useLanguage } from '@/composables/useLanguage';
import { useScore } from '@/composables/useScore';
import StudentAppLayout from '@/layouts/StudentAppLayout.vue';
import type { BreadcrumbItem, StudentCourseShowProps } from '@/types';
import { Deferred, Head, InfiniteScroll, Link, router } from '@inertiajs/vue3';
import { format, isThisMonth, isThisWeek, isToday, isYesterday, parseISO } from 'date-fns';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Calendar, CalendarCheck, Eye, LoaderCircle, Play } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

dayjs.extend(relativeTime);

const { getLanguageLogo, getLanguageColor } = useLanguage();
const { getScoreDisplay, getScoreBackgroundClass } = useScore();

const props = defineProps<StudentCourseShowProps>();

const isInitialLoadDone = ref(false);
const activeTab = ref(props.activeTab || 'activities');
const studentsPage = ref(1);
const scrollOffset = ref(0);

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

// Date formatting helpers
function formatDateHeader(dateString: string): string {
    const date = parseISO(dateString);

    if (isToday(date)) return 'Today';
    if (isYesterday(date)) return 'Yesterday';
    if (isThisWeek(date)) return format(date, 'EEEE');
    if (isThisMonth(date)) return format(date, 'MMMM d');
    return format(date, 'MMMM d, yyyy');
}

// Group activities by date
const groupedActivitiesByDate = computed(() => {
    if (!props.activities?.data) return [];

    const groups: Record<string, any[]> = {};

    props.activities.data.forEach((activity) => {
        const date = parseISO(activity.created_at);
        const dateKey = format(date, 'yyyy-MM-dd');

        if (!groups[dateKey]) {
            groups[dateKey] = [];
        }
        groups[dateKey].push(activity);
    });

    return Object.keys(groups)
        .sort((a, b) => b.localeCompare(a))
        .map((key) => ({
            date: key,
            dateLabel: formatDateHeader(groups[key][0]?.created_at),
            items: groups[key],
        }));
});

// Group completed activities by date
const groupedCompletedActivitiesByDate = computed(() => {
    if (!props.completedActivities?.data) return [];

    const groups: Record<string, any[]> = {};

    props.completedActivities.data.forEach((activity) => {
        const date = parseISO(activity.created_at);
        const dateKey = format(date, 'yyyy-MM-dd');

        if (!groups[dateKey]) {
            groups[dateKey] = [];
        }
        groups[dateKey].push(activity);
    });

    return Object.keys(groups)
        .sort((a, b) => b.localeCompare(a))
        .map((key) => ({
            date: key,
            dateLabel: formatDateHeader(groups[key][0]?.created_at),
            items: groups[key],
        }));
});

watch(activeTab, (newTab) => {
    const page = newTab === 'students' ? studentsPage.value : 1;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { tab: newTab, page },
        preserveState: true,
        only:
            newTab === 'students'
                ? ['students', 'activeTab']
                : newTab === 'completed'
                  ? ['completedActivities', 'activeTab']
                  : ['activities', 'activeTab'],
        reset: newTab === 'students' ? [] : newTab === 'completed' ? ['completedActivities'] : ['activities'],
    });
});

const handleStudentsPageChange = (page: number) => {
    studentsPage.value = page;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { page, tab: 'students' },
        preserveScroll: true,
        preserveState: true,
        only: ['students'],
    });
};

// Handle scroll animation
const handleScroll = () => {
    scrollOffset.value = window.scrollY;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head :title="`${course.name}`" />

    <StudentAppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <!-- Header Card -->
            <Card>
                <CardHeader>
                    <AspectRatio :ratio="16 / 4" class="rounded-lg bg-muted">
                        <img :src="`/images/cover-photos/${course.cover_photo}`" alt="Course cover" class="h-full w-full rounded-lg object-cover" />
                    </AspectRatio>
                </CardHeader>
                <CardContent>
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
                </CardContent>
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
                                    <div>
                                        <div v-if="activities?.data.length">
                                            <InfiniteScroll data="activities">
                                                <div class="space-y-6">
                                                    <template v-for="(group, groupIndex) in groupedActivitiesByDate" :key="group.date">
                                                        <div class="space-y-3">
                                                            <div class="flex items-center gap-2 px-2">
                                                                <Calendar class="size-4 text-muted-foreground" />
                                                                <h3 class="text-sm font-semibold text-foreground">
                                                                    {{ group.dateLabel }}
                                                                </h3>
                                                                <Badge variant="secondary" class="ml-auto text-xs">
                                                                    {{ group.items.length }}
                                                                </Badge>
                                                            </div>

                                                            <div class="space-y-4">
                                                                <Item
                                                                    variant="outline"
                                                                    v-for="activity in group.items"
                                                                    :key="activity.id"
                                                                    class="p-6 shadow-md transition-shadow duration-200 hover:shadow-lg"
                                                                >
                                                                    <ItemContent>
                                                                        <ItemTitle class="line-clamp-1 text-xl font-bold capitalize">{{
                                                                            activity.activity_title
                                                                        }}</ItemTitle>
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
                                                                                        <Button size="icon-xl" class="button-3d rounded-full">
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

                                                            <Separator v-if="groupIndex < groupedActivitiesByDate.length - 1" class="my-2" />
                                                        </div>
                                                    </template>
                                                </div>

                                                <template #loading>
                                                    <div class="mt-10 flex items-center justify-center gap-2 text-muted-foreground">
                                                        <LoaderCircle class="h-6 w-6 animate-spin" />
                                                        <div class="text-md font-semibold sm:text-lg">Loading more activities...</div>
                                                    </div>
                                                </template>
                                            </InfiniteScroll>
                                        </div>
                                        <div v-else class="py-8 text-center text-muted-foreground">No activities found</div>
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-md font-semibold text-muted-foreground sm:text-lg">Loading activities...</span>
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
                                    <div>
                                        <div v-if="completedActivities?.data.length">
                                            <InfiniteScroll data="completedActivities">
                                                <div class="space-y-6">
                                                    <template v-for="(group, groupIndex) in groupedCompletedActivitiesByDate" :key="group.date">
                                                        <div class="space-y-3">
                                                            <div class="flex items-center gap-2 px-2">
                                                                <Calendar class="size-4 text-muted-foreground" />
                                                                <h3 class="text-sm font-semibold text-foreground">
                                                                    {{ group.dateLabel }}
                                                                </h3>
                                                                <Badge variant="secondary" class="ml-auto text-xs">
                                                                    {{ group.items.length }}
                                                                </Badge>
                                                            </div>

                                                            <div class="space-y-4">
                                                                <Item
                                                                    v-for="activity in group.items"
                                                                    :key="activity.id"
                                                                    :class="[
                                                                        activity.submitted_at
                                                                            ? getScoreBackgroundClass(activity.score, activity.total_score)
                                                                            : 'bg-slate-300 dark:bg-slate-600',
                                                                        'relative',
                                                                    ]"
                                                                    class="overflow-hidden p-6"
                                                                >
                                                                    <!-- Diagonal stripe overlay for submitted items with scroll animation -->
                                                                    <div
                                                                        v-if="activity.submitted_at"
                                                                        class="submitted-overlay-stripes"
                                                                        :style="{ backgroundPosition: `${scrollOffset * 0.5}px 0` }"
                                                                    ></div>

                                                                    <div
                                                                        v-if="!activity.is_open"
                                                                        class="pointer-events-none absolute inset-0 z-20 rounded-lg bg-slate-900/40 dark:bg-slate-950/50"
                                                                    ></div>
                                                                    <ItemContent
                                                                        :class="!activity.is_open ? 'relative z-30 opacity-80' : 'relative z-10'"
                                                                    >
                                                                        <ItemTitle class="line-clamp-1 text-xl font-bold text-white capitalize">
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
                                                                                    class="sm:text-md flex items-center gap-1.5 text-xs font-semibold text-white/90"
                                                                                >
                                                                                    <CalendarCheck class="h-3.5 w-3.5" />
                                                                                    Closed
                                                                                </span>
                                                                                <div class="hidden md:block">
                                                                                    <span class="flex items-center gap-1.5 text-xs text-white/90">
                                                                                        <Calendar class="h-3.5 w-3.5" />
                                                                                        {{ dayjs(activity.created_at).format('MMM D, YYYY h:mm A') }}
                                                                                    </span>
                                                                                    <span
                                                                                        v-if="activity.submitted_at"
                                                                                        class="sm:text-md flex items-center gap-1.5 text-xs text-white/90"
                                                                                    >
                                                                                        <Calendar class="h-3.5 w-3.5" />
                                                                                        Submitted:
                                                                                        {{
                                                                                            dayjs(activity.submitted_at).format('MMM D, YYYY h:mm A')
                                                                                        }}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </ItemDescription>
                                                                    </ItemContent>
                                                                    <ItemActions
                                                                        :class="
                                                                            !activity.is_open
                                                                                ? 'relative z-30 flex items-center gap-4 opacity-80'
                                                                                : 'relative z-10 flex items-center gap-4'
                                                                        "
                                                                    >
                                                                        <div class="flex flex-col items-end gap-1">
                                                                            <span class="text-xs font-medium tracking-wider text-white/70 uppercase"
                                                                                >Score</span
                                                                            >
                                                                            <span
                                                                                v-if="activity.submitted_at"
                                                                                class="text-lg font-bold text-white sm:text-2xl"
                                                                            >
                                                                                {{ getScoreDisplay(activity.score, activity.total_score).text }}
                                                                            </span>
                                                                            <span v-else class="text-sm font-semibold text-white/70 italic">
                                                                                Not Submitted
                                                                            </span>
                                                                        </div>
                                                                        <TooltipProvider>
                                                                            <Tooltip>
                                                                                <TooltipTrigger as-child>
                                                                                    <div>
                                                                                        <Link
                                                                                            v-if="activity.is_open"
                                                                                            :href="`/student/submit/${activity.token}`"
                                                                                        >
                                                                                            <Button
                                                                                                size="icon-xl"
                                                                                                variant="secondary"
                                                                                                class="button-3d-secondary rounded-full border-white/30 bg-white/20 text-white backdrop-blur-sm hover:bg-white/30"
                                                                                            >
                                                                                                <Eye class="size-5" />
                                                                                            </Button>
                                                                                        </Link>
                                                                                        <Button
                                                                                            v-else
                                                                                            size="icon-xl"
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

                                                            <Separator v-if="groupIndex < groupedCompletedActivitiesByDate.length - 1" class="my-2" />
                                                        </div>
                                                    </template>
                                                </div>

                                                <template #loading>
                                                    <div class="mt-10 flex items-center justify-center gap-2 text-muted-foreground">
                                                        <LoaderCircle class="h-6 w-6 animate-spin" />
                                                        <div class="text-md font-semibold sm:text-lg">Loading more activities...</div>
                                                    </div>
                                                </template>
                                            </InfiniteScroll>
                                        </div>
                                        <div v-else class="py-8 text-center text-muted-foreground">No completed activities found</div>
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-md font-semibold text-muted-foreground sm:text-lg">Loading completed activities...</span>
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
                                <span class="text-md font-semibold text-muted-foreground sm:text-lg">Loading students...</span>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </StudentAppLayout>
</template>

<style scoped>
/* Diagonal stripe overlay for submitted items */
.submitted-overlay-stripes {
    position: absolute;
    top: -100%;
    left: -100%;
    right: -100%;
    bottom: -100%;
    background: repeating-linear-gradient(45deg, transparent, transparent 120px, rgba(255, 255, 255, 0.08) 120px, rgba(255, 255, 255, 0.08) 240px);
    background-size: 339px 339px; /* sqrt(240^2 + 240^2) for seamless 45deg pattern */
    pointer-events: none;
    z-index: 1;
    border-radius: inherit;
    transition: background-position 0.5s ease-out;
}

/* Cartoonish 3D Button for Primary Button (Play button) */
.button-3d {
    position: relative;
    transform: translateY(-4px);
    box-shadow: 0 5px 0 0 rgb(0, 3, 153) !important;
    transition: all 0.1s ease !important;
}

.button-3d:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 0 0 rgb(0, 3, 153) !important;
}

.button-3d:active {
    transform: translateY(0px);
    box-shadow: 0 0px 0 0 rgb(0, 3, 153) !important;
}

/* Cartoonish 3D Button for Secondary Button (Eye button) */
.button-3d-secondary {
    position: relative;
    transform: translateY(-4px);
    box-shadow: 0 5px 0 0 rgba(0, 0, 0, 0.438);
    transition: all 0.1s ease;
}

.button-3d-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 0 0 rgba(0, 0, 0, 0.438);
}

.button-3d-secondary:active {
    transform: translateY(0px);
    box-shadow: 0 0px 0 0 rgba(0, 0, 0, 0.438);
}

/* Ensure smooth transitions for icon inside buttons */
.button-3d:hover svg,
.button-3d-secondary:hover svg {
    transition: transform 0.1s ease;
}

.button-3d:active svg,
.button-3d-secondary:active svg {
    transform: scale(0.95);
}
</style>
