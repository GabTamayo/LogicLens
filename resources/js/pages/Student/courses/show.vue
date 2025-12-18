<script setup lang="ts">
import StudentAppLayout from "@/layouts/StudentAppLayout.vue";
import type { BreadcrumbItem, StudentCourseShowProps } from "@/types";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemMedia,
    ItemTitle,
} from "@/components/ui/item";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/ui/tooltip";
import { Deferred, router, Link, Head } from "@inertiajs/vue3";
import { LoaderCircle, Play, Calendar, CalendarCheck } from "lucide-vue-next";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import { ref, watch, computed } from "vue";
import DataTable from "@/components/DataTable.vue";
import { columns as studentColumns } from "@/components/students(student)/columns";
import PaginationComponent from "@/components/Pagination.vue";
import { useLanguage } from "@/composables/useLanguage";

dayjs.extend(relativeTime);

const { getLanguageLogo, getLanguageColor } = useLanguage();

const props = defineProps<StudentCourseShowProps>();

const isInitialLoadDone = ref(false);
const activeTab = ref(props.activeTab || "activities");
const activitiesPage = ref(1);
const studentsPage = ref(1);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Courses",
        href: "/student/courses",
    },
    {
        title: props.course.name,
        href: `/student/courses/${props.course.id}`,
    },
];

watch(activeTab, (newTab) => {
    const page = newTab === "students" ? studentsPage.value : activitiesPage.value;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { tab: newTab, page },
        preserveScroll: true,
        preserveState: true,
        only: newTab === "students" ? ["students", "activeTab"] : ["activities", "activeTab"],
    });
});

const handleActivitiesPageChange = (page: number) => {
    activitiesPage.value = page;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { page, tab: "activities" },
        preserveScroll: true,
        preserveState: true,
        only: ["activities"],
    });
};

const handleStudentsPageChange = (page: number) => {
    studentsPage.value = page;
    router.visit(`/student/courses/${props.course.id}`, {
        data: { page, tab: "students" },
        preserveScroll: true,
        preserveState: true,
        only: ["students"],
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
                                <TabsTrigger value="activities"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pb-3 pt-2 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none">
                                    Activities
                                </TabsTrigger>
                                <TabsTrigger value="completed"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pb-3 pt-2 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none">
                                    Completed
                                </TabsTrigger>
                                <TabsTrigger value="students"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pb-3 pt-2 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none">
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
                                            <Item variant="outline" v-for="activity in activities.data"
                                                :key="activity.id">
                                                <ItemContent>
                                                    <ItemTitle class="capitalize text-xl font-bold">{{
                                                        activity.activity_title
                                                    }}</ItemTitle>
                                                    <ItemDescription>
                                                        <span :class="getLanguageColor(activity.activity_language)"
                                                            class="inline-flex items-center mb-2 gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium border">
                                                            <img v-if="getLanguageLogo(activity.activity_language)"
                                                                :src="getLanguageLogo(activity.activity_language)!"
                                                                :alt="activity.activity_language"
                                                                class="h-4 w-4 object-contain" />
                                                            {{ activity.activity_language }}
                                                        </span>
                                                        <span class="flex items-center gap-1.5">
                                                            <Calendar
                                                                class="h-3.5 w-3.5 text-green-600 dark:text-green-400" />
                                                            {{
                                                                dayjs(activity.created_at).format("MMM D, YYYY h:mm A")
                                                            }}
                                                        </span>
                                                        <span v-if="activity.expires_at"
                                                            class="flex items-center gap-1.5">
                                                            <Calendar
                                                                class="h-3.5 w-3.5 text-red-600 dark:text-red-400" />
                                                            {{
                                                                dayjs(activity.expires_at).format("MMM D, YYYY h:mm A")
                                                            }}
                                                        </span>
                                                        <span v-else
                                                            class="flex items-center gap-1.5 text-muted-foreground">
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
                                        <div v-else class="text-center py-8 text-muted-foreground">
                                            No activities found
                                        </div>
                                        <PaginationComponent v-if="activities && activities.data.length > 0"
                                            :pagination="activities" @page-change="handleActivitiesPageChange" />
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading activities...</span>
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
                                    <DataTable :columns="studentColumns" :data="students.data"
                                        :pagination="students as any" @page-change="handleStudentsPageChange" />
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
