<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';
import { Circle, Eye, CalendarClock, FileText, Pencil, Code } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/components/ui/tooltip'
import { Link } from '@inertiajs/vue3';
import { useLanguage } from '@/composables/useLanguage';
import { useDeadline } from '@/composables/useDeadline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

interface ActivityWithContent {
    id: string;
    activity_id: string;
    activity_title: string;
    activity_language: string;
    activity_content?: string;
    token: string;
    is_open: boolean;
    expires_at: string | null;
    created_at: string;
    submissions_count: number;
}

const props = defineProps<{
    activity: ActivityWithContent | null;
}>();

const { getLanguageConfig } = useLanguage();
const { formatRelativeDeadline, getDeadlineStatus } = useDeadline();

const formattedDate = computed(() => {
    if (!props.activity?.created_at) {
        return '';
    }
    return dayjs(props.activity.created_at).fromNow();
});

const formattedExpiry = computed(() => {
    return formatRelativeDeadline(props.activity?.expires_at ?? null);
});

const deadlineStatus = computed(() => {
    return getDeadlineStatus(props.activity?.expires_at ?? null);
});
</script>

<template>
    <div v-if="!activity" class="flex h-full items-center justify-center">
        <div class="flex flex-col items-center text-center">
            <div class="mb-4 rounded-full bg-muted p-3">
                <Code class="h-6 w-6 text-muted-foreground" />
            </div>
            <p class="text-lg font-medium mb-1">Select an activity</p>
            <p class="text-sm text-muted-foreground">Choose an activity to view details</p>
        </div>
    </div>

    <div v-else class="flex h-full flex-col">
        <div class="flex-1 overflow-y-auto p-6">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Header -->
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 space-y-1">
                            <Button variant="link" class="p-0 h-auto text-start" as-child>
                                <Link :href="`/activities/${activity.activity_id}`" prefetch="mount">
                                    <h2 class="text-2xl font-bold tracking-tight">
                                        {{ activity.activity_title }}
                                    </h2>
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <Badge variant="outline" :class="[
                                'flex items-center gap-1.5',
                                getLanguageConfig(activity.activity_language).colors,
                            ]">
                                <img v-if="getLanguageConfig(activity.activity_language).logo"
                                    :src="getLanguageConfig(activity.activity_language).logo"
                                    :alt="activity.activity_language" class="h-3.5 w-3.5 object-contain" />
                                <span class="text-xs font-medium">{{
                                    activity.activity_language
                                }}</span>
                            </Badge>
                        </div>

                        <Separator orientation="vertical" class="h-4" />

                        <div class="flex items-center gap-1.5">
                            <CalendarClock class="h-3.5 w-3.5" />
                            <span>Assigned {{ formattedDate }}</span>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- Statistics -->
                <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardDescription>Total Submissions</CardDescription>
                            <CardTitle class="text-3xl">{{
                                activity.submissions_count
                            }}</CardTitle>
                            <CardAction>
                                <Link :href="`/activities/${activity.activity_id}/links/${activity.id}`"
                                    prefetch="mount">
                                    <Button class="w-fit" size="sm">
                                        <span class="block md:hidden lg:hidden xl:block">View Submissions</span>
                                    </Button>
                                </Link>
                            </CardAction>
                        </CardHeader>
                    </Card>

                    <Card>
                        <CardHeader class="pb-3">
                            <CardDescription>Deadline</CardDescription>
                            <CardTitle v-if="deadlineStatus" class="flex items-center gap-2 text-lg"
                                :class="deadlineStatus.class">
                                <component :is="deadlineStatus.icon" class="h-5 w-5" />
                                {{ formattedExpiry }}
                            </CardTitle>
                            <CardTitle v-else class="text-lg text-muted-foreground">
                                {{ formattedExpiry }}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                <!-- Activity Content -->
                <Card>
                    <CardHeader class="flex justify-between items-center">
                        <div>
                            <CardTitle>Activity Content</CardTitle>
                            <CardDescription>Instructions and details for this activity</CardDescription>
                        </div>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button v-if="activity.activity_content" variant="outline" size="icon-sm"
                                        class="rounded-full">
                                        <Link :href="`/activities/${activity.activity_id}`" prefetch="mount">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Edit Content</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activity.activity_content" class="prose prose-sm dark:prose-invert max-w-none"
                            v-html="activity.activity_content"></div>
                        <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-4 rounded-full bg-muted p-3">
                                <FileText class="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 class="mb-1 font-semibold text-lg">No content added yet</h3>
                            <p class="mb-4 max-w-sm text-sm text-muted-foreground">
                                Add instructions, requirements, or details for this activity
                            </p>
                            <Link :href="`/activities/${activity.activity_id}`" prefetch="mount">
                                <Button variant="outline" size="sm">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Add Content
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
