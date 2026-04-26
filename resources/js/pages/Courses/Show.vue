<script setup lang="ts">
import ActivityContent from '@/components/activities/ActivityContent.vue';
import ActivitySidebar from '@/components/activities/ActivitySidebar.vue';
import AlertDialogDelete from '@/components/AlertDialogDelete.vue';
import DataTable from '@/components/DataTable.vue';
import { columns as studentColumns } from '@/components/students/columns';
import AspectRatio from '@/components/ui/aspect-ratio/AspectRatio.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/sonner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, CourseShowProps } from '@/types';
import { Deferred, Head, router } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { CalendarCheck, Copy, Loader, LoaderCircle, Menu, Pencil } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

dayjs.extend(relativeTime);

const props = defineProps<CourseShowProps>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Courses', href: '/courses' },
    { title: props.course.name, href: `/courses/${props.course.id}` },
]);

const formattedDate = computed(() => {
    return new Date(props.course.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const activeTab = ref(props.activeTab || 'activities');
const isInitialLoadDone = ref(false);
const activitiesPage = ref(1);
const studentsPage = ref(1);
const selectedActivity = ref(props.activities?.data[0] || null);
const mobileSheetOpen = ref(false);

function copy(text: string) {
    navigator.clipboard.writeText(text);
    toast('Copied to clipboard', {});
}

function handleActivitySelect(activity: any) {
    selectedActivity.value = activity;
    mobileSheetOpen.value = false;
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
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/courses/${course.id}`" type="course" buttonText="Delete Course" :itemName="course.name" />
        </template>
        <div class="flex h-full flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Header Card -->
            <Card>
                <CardHeader>
                    <AspectRatio :ratio="6 / 1.5" class="rounded-lg bg-muted">
                        <img :src="`/images/cover-photos/${course.cover_photo}`" alt="Course cover" class="h-full w-full rounded-lg object-cover" />
                    </AspectRatio>
                </CardHeader>
                <CardContent>
                    <div class="flex items-start justify-between">
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
                                        <Button variant="outline" size="icon-sm" @click.stop="copy(course.access_code)" aria-label="Copy access-code">
                                            <Copy class="size-4" />
                                        </Button>
                                        <span>{{ course.access_code }}</span>
                                    </div>
                                </CardDescription>
                            </div>
                        </div>
                        <CardAction class="static m-0">
                            <ModalLink :href="`/courses/${course.id}/edit`" #default="{ loading }" :close-explicitly="true">
                                <Button variant="secondary" size="lg" :disabled="loading">
                                    <Loader v-if="loading" class="h-4 w-4 animate-spin" />
                                    <Pencil v-else class="mr-1 size-4" />
                                    Edit course
                                </Button>
                            </ModalLink>
                        </CardAction>
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
                                    value="students"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pt-2 pb-3 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none"
                                >
                                    Students
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <TabsContent value="activities" class="m-0 p-0">
                            <template v-if="props.activities">
                                <Deferred data="activities" @resolve="isInitialLoadDone = true">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading activities...</span>
                                        </div>
                                    </template>
                                    <div class="flex h-[calc(100vh-20rem)]">
                                        <!-- Desktop Sidebar -->
                                        <div class="hidden w-80 border-r md:block">
                                            <ActivitySidebar
                                                :activities="props.activities.data"
                                                :selected-activity-id="selectedActivity?.id"
                                                @select="handleActivitySelect"
                                            />
                                        </div>

                                        <!-- Main Content -->
                                        <div class="flex flex-1 flex-col">
                                            <!-- Mobile Header with Menu Button -->
                                            <div class="flex items-center gap-2 border-b p-4 md:hidden">
                                                <Sheet v-model:open="mobileSheetOpen">
                                                    <SheetTrigger as-child>
                                                        <Button variant="outline" size="icon">
                                                            <Menu class="h-5 w-5" />
                                                        </Button>
                                                    </SheetTrigger>
                                                    <SheetContent side="left" class="w-80 p-0">
                                                        <SheetHeader class="border-b p-4">
                                                            <SheetTitle>Activities</SheetTitle>
                                                        </SheetHeader>
                                                        <ActivitySidebar
                                                            :activities="props.activities.data"
                                                            :selected-activity-id="selectedActivity?.id"
                                                            @select="handleActivitySelect"
                                                        />
                                                    </SheetContent>
                                                </Sheet>
                                                <h3 class="line-clamp-1 text-sm font-semibold">
                                                    {{ selectedActivity?.activity_title || 'Select an activity' }}
                                                </h3>
                                            </div>

                                            <!-- Activity Content -->
                                            <div class="flex-1 overflow-y-auto">
                                                <ActivityContent :activity="selectedActivity" />
                                            </div>
                                        </div>
                                    </div>
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
                                    <DataTable
                                        :columns="studentColumns"
                                        :data="props.students.data"
                                        :pagination="props.students as any"
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
    </AppLayout>

    <Toaster rich-colors />
</template>
