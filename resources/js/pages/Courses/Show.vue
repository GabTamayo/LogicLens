<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { Separator } from "@/components/ui/separator";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import type { BreadcrumbItem, CourseShowProps } from "@/types";
import { computed, ref } from "vue";
import { Circle, ExternalLink, CalendarCheck, Copy } from "lucide-vue-next";
import { Deferred, Head, Link } from "@inertiajs/vue3";
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

const activitiesLoaded = computed(() => props.activities !== undefined);
const studentsLoaded = computed(() => props.students !== undefined);
const activeTab = ref('activities');
const isInitialLoadDone = ref(false);

function copy(text: string) {
    navigator.clipboard.writeText(text)
    toast('Copied to clipboard', {
    })
}
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
                                    <Button variant="ghost" size="icon" @click.stop="copy(course.access_code)"
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
                            <template v-if="activitiesLoaded">
                                <Deferred data="activities" @resolve="isInitialLoadDone = true">
                                    <template #fallback>
                                        <div class="space-y-3">
                                            <Skeleton class="h-12 w-full" v-for="i in 3" :key="i" />
                                        </div>
                                    </template>
                                    <div v-if="activities && activities.length > 0" class="rounded-md border">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Activity</TableHead>
                                                    <TableHead>Language</TableHead>
                                                    <TableHead>Status</TableHead>
                                                    <TableHead>Submissions</TableHead>
                                                    <TableHead>Created</TableHead>
                                                    <TableHead class="text-right">Action</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="activity in activities" :key="activity.id"
                                                    class="group hover:bg-muted/50">
                                                    <TableCell>
                                                        <span class="font-medium">{{ activity.activity_title }}</span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="outline">{{ activity.activity_language }}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="flex items-center gap-1.5">
                                                            <Circle :class="[
                                                                'h-2 w-2 fill-current',
                                                                activity.is_open ? 'text-green-500' : 'text-red-500'
                                                            ]" />
                                                            <span class="text-sm">{{ activity.is_open ? 'Open' :
                                                                'Closed'
                                                                }}</span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <span class="text-sm">{{ activity.submissions_count }}</span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <span class="text-xs text-muted-foreground">
                                                            {{ dayjs(activity.created_at).fromNow() }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        <Link
                                                            :href="`/activities/${activity.activity_id}/links/${activity.id}`"
                                                            prefetch="mount"
                                                            class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                                                            <span>View</span>
                                                            <ExternalLink class="h-3.5 w-3.5" />
                                                        </Link>
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                    <div v-else class="flex items-center justify-center rounded-md border p-12">
                                        <p class="text-sm text-muted-foreground text-center">
                                            No activities assigned to this course yet.
                                        </p>
                                    </div>
                                </Deferred>
                            </template>

                            <div v-else class="space-y-3">
                                <Skeleton class="h-12 w-full" v-for="i in 3" :key="i" />
                            </div>
                        </TabsContent>

                        <TabsContent value="students" class="m-0 p-6">
                            <template v-if="studentsLoaded">
                                <Deferred data="students">
                                    <template #fallback>
                                        <div class="space-y-3">
                                            <Skeleton class="h-12 w-full" v-for="i in 3" :key="i" />
                                        </div>
                                    </template>
                                    <div class="flex items-center justify-center rounded-md border p-12">
                                        <p class="text-sm text-muted-foreground text-center">
                                            Student enrollment feature coming soon.
                                        </p>
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="space-y-3">
                                <Skeleton class="h-12 w-full" v-for="i in 3" :key="i" />
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <Toaster rich-colors />
</template>
