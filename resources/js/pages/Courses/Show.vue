<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Separator } from "@/components/ui/separator";
import DataTable from "@/components/DataTable.vue";
import { columns as activityColumns } from "@/components/activities/columns";
import { columns as studentColumns } from "@/components/students/columns";
import type { BreadcrumbItem, CourseShowProps } from "@/types";
import { computed, ref, watch } from "vue";
import { CalendarCheck, Copy, LoaderCircle } from "lucide-vue-next";
import { Deferred, Head, router } from "@inertiajs/vue3";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import Button from "@/components/ui/button/Button.vue";

dayjs.extend(relativeTime);

const props = defineProps<CourseShowProps>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: "Courses", href: "/courses" },
    { title: props.course.name, href: `/courses/${props.course.id}` },
]);

const formattedDate = computed(() => {
    return new Date(props.course.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const activeTab = ref(props.activeTab || 'activities');
const isInitialLoadDone = ref(false);
const activitiesPage = ref(1);
const studentsPage = ref(1);

function copy(text: string) {
    navigator.clipboard.writeText(text)
    toast('Copied to clipboard', {
    })
}

// Tab change handler
watch(activeTab, (newTab) => {
    const page = newTab === 'students' ? studentsPage.value : activitiesPage.value;
    router.visit(`/courses/${props.course.id}`, {
        data: { tab: newTab, page },
        preserveScroll: true,
        preserveState: true,
        only: newTab === 'students' ? ['students', 'activeTab'] : ['activities', 'activeTab'],
    });
});

// Pagination handlers
const handleActivitiesPageChange = (page: number) => {
    activitiesPage.value = page;
    router.visit(`/courses/${props.course.id}`, {
        data: { page, tab: 'activities' },
        preserveScroll: true,
        preserveState: true,
        only: ['activities'],
    });
};

const handleStudentsPageChange = (page: number) => {
    studentsPage.value = page;
    router.visit(`/courses/${props.course.id}`, {
        data: { page, tab: 'students' },
        preserveScroll: true,
        preserveState: true,
        only: ['students'],
    });
};
</script>

<template>

    <Head :title="`${course.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col gap-6 overflow-x-auto rounded-xl p-4">
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
                                <Separator orientation="vertical" class="h-4" />
                                <span class="flex items-center gap-1.5">
                                    <CalendarCheck class="h-3.5 w-3.5" />
                                    {{ formattedDate }}
                                </span>
                                <Separator orientation="vertical" class="h-4" />
                                <div class="flex items-center gap-1">
                                    <Button variant="outline" size="icon-sm" @click.stop="copy(course.access_code)"
                                        aria-label="Copy access-code">
                                        <Copy class="size-4" />
                                    </Button>
                                    <span>{{ course.access_code }}</span>
                                </div>
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
                                <TabsTrigger value="students"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pb-3 pt-2 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none">
                                    Students
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <TabsContent value="activities" class="m-0 p-6">
                            <template v-if="props.activities">
                                <Deferred data="activities" @resolve="isInitialLoadDone = true">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading activities...</span>
                                        </div>
                                    </template>
                                    <DataTable :columns="activityColumns" :data="props.activities.data"
                                        :pagination="props.activities as any" @page-change="handleActivitiesPageChange" />
                                </Deferred>
                            </template>

                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading activities...</span>
                            </div>
                        </TabsContent>

                        <TabsContent value="students" class="m-0 p-6">
                            <template v-if="props.students">
                                <Deferred data="students">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading students...</span>
                                        </div>
                                    </template>
                                    <DataTable :columns="studentColumns" :data="props.students.data"
                                        :pagination="props.students as any" @page-change="handleStudentsPageChange" />
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
    </AppLayout>

    <Toaster rich-colors />
</template>
