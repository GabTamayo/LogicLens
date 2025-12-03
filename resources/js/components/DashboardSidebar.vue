<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { TrendingDown, TrendingUp, Circle, FileScan, Flag, SquareArrowOutUpRight, Eye, FlagOff, Calendar, ChevronRight, LoaderCircle, Code2, Clock } from "lucide-vue-next"
import { Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { Item, ItemContent, ItemDescription, ItemFooter, ItemHeader, ItemMedia, ItemTitle, ItemGroup, ItemSeparator, ItemActions } from '@/components/ui/item'
import { ChartConfig, ChartContainer, ChartTooltip, ChartTooltipContent, componentToString, } from "@/components/ui/chart"
import { Donut } from "@unovis/ts"
import { VisDonut, VisSingleContainer } from "@unovis/vue"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/components/ui/tooltip'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger, } from '@/components/ui/alert-dialog'
import Button from "./ui/button/Button.vue"
import { HoverCard, HoverCardContent, HoverCardTrigger, } from '@/components/ui/hover-card'
import Badge from './ui/badge/Badge.vue';
import { ModalLink } from '@inertiaui/modal-vue'
import { ActiveLinksData, FlaggedDetectionsPagination, UpcomingThisWeekPagination } from '@/types';
import { formatDistanceToNow, parseISO, differenceInDays } from 'date-fns';
import { router, Link, InfiniteScroll } from '@inertiajs/vue3';

const props = defineProps<{
    activeLinksData: ActiveLinksData;
    upcomingThisWeek: UpcomingThisWeekPagination;
    totalUpcomingThisWeek: number;
    flaggedDetections: FlaggedDetectionsPagination;
    totalFlaggedDetections: number;
}>()

const activeLinksChartData = computed(() => [
    { label: "No Deadline", value: props.activeLinksData?.noDeadline ?? 0, fill: "var(--chart-1)" },
    { label: "With Deadline", value: props.activeLinksData?.withDeadline ?? 0, fill: "var(--chart-2)" },
]);
const activeLinksChartConfig: ChartConfig = {
    value: { label: "Active Links", color: undefined },
    "With Deadline": { label: "With Deadline", color: "var(--chart-2)" },
    "No Deadline": { label: "No Deadline", color: "var(--chart-1)" },
};
function formatRelativeDeadline(expires_at: string | null) {
    if (!expires_at) return 'No deadline'
    const date = parseISO(expires_at)

    const distance = formatDistanceToNow(date, { addSuffix: true })
    return distance
}
function flagDetection(detection: FlaggedDetections): void {
    router.patch(`/detections/${detection.id}/flag`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['flaggedDetections', 'totalFlaggedDetections'] });
        },
    });
}
function getInitials(name: string | null | undefined): string {
    if (!name) return '';
    return name
        .split(' ')
        .map(part => part.charAt(0).toUpperCase())
        .join('');
}
function formatScore(score: number): string {
    return (score * 100).toFixed(2);
}
function formatScoreList(score: number): string {
    return (score * 100).toFixed(0);
}
function getSimilarityBadge(score: number) {
    if (score >= 0.90) {
        return { variant: 'destructive' as const, label: 'Very High', class: 'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-400 border-red-300 dark:border-red-800' }
    }
    if (score >= 0.85) {
        return { variant: 'customOrange' as const, label: 'High', class: 'bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-400 border-orange-300 dark:border-orange-800' }
    }
    if (score >= 0.75) {
        return { variant: 'customYellow' as const, label: 'Moderate', class: 'bg-yellow-100 dark:bg-yellow-950/50 text-yellow-700 dark:text-yellow-400 border-yellow-300 dark:border-yellow-800' }
    }
    return { variant: 'outline' as const, label: 'Low', class: 'bg-green-100 dark:bg-green-950/50 text-green-700 dark:text-green-400 border-green-300 dark:border-green-800' }
}

function getDeadlineUrgency(expires_at: string | null) {
    if (!expires_at) return 'none';
    const days = differenceInDays(parseISO(expires_at), new Date());
    if (days < 1) return 'overdue';
    if (days <= 2) return 'urgent';
    if (days <= 5) return 'soon';
    return 'normal';
}
function getUrgencyColor(urgency: string) {
    switch (urgency) {
        case 'overdue': return 'text-red-600 dark:text-red-400';
        case 'urgent': return 'text-orange-600 dark:text-orange-400';
        case 'soon': return 'text-yellow-600 dark:text-yellow-400';
        default: return 'text-muted-foreground';
    }
}
const getLanguageLogo = (language: string) => {
    switch (language) {
        case 'java':
            return '/images/java-logo-png.png';
        case 'python':
            return '/images/python-logo-png.png';
        default:
            return null;
    }
};
</script>

<template>
    <ModalLink href="dashboard/active-links" #default="{ loading }">
        <ChartContainer :config="activeLinksChartConfig" class="mx-auto aspect-square max-h-[170px]" :style="{
            '--vis-donut-central-label-font-size': 'var(--text-3xl)',
            '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
            '--vis-donut-central-label-text-color': 'var(--foreground)',
            '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
        }">
            <VisSingleContainer :data="activeLinksChartData" :margin="{ bottom: 0 }">
                <VisDonut :value="d => d.value" :color="d => activeLinksChartConfig[d.label].color" :arc-width="30"
                    :central-label="props.activeLinksData.total.toLocaleString()"
                    :central-sub-label="loading ? 'Active Links...' : 'Active Links'" />
                <ChartTooltip :triggers="{
                    [Donut.selectors.segment]: componentToString(activeLinksChartConfig, ChartTooltipContent, { hideLabel: true })!,
                }" />
            </VisSingleContainer>
        </ChartContainer>
    </ModalLink>

    <Separator class="mt-4" />

    <div class="text-left w-full">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2.5">
                <div class="p-1.5 rounded-md bg-primary/10">
                    <Calendar class="size-4 text-primary" />
                </div>
                <h3 class="font-semibold text-sm">Due This Week</h3>
            </div>
            <Badge variant="secondary" class="text-xs font-medium px-2 py-0.5">
                {{ props.totalUpcomingThisWeek }}
            </Badge>
        </div>

        <div class="h-[205px]">
            <ItemGroup v-if="upcomingThisWeek.data && upcomingThisWeek.data.length > 0">
                <ScrollArea class="h-55 w-full rounded-md">
                    <div class="p-1">
                        <InfiniteScroll data="upcomingThisWeek">
                            <template v-for="(item, index) in upcomingThisWeek.data" :key="item.id">
                                <Item role="listitem" as-child>
                                    <Link :href="`/activities/${item.activity_id}`">
                                    <ItemContent>
                                        <ItemTitle class="text-sm font-bold">
                                            {{ item.name }}
                                        </ItemTitle>
                                        <ItemDescription class="text-xs font-semibold flex items-center gap-1">
                                            <img v-if="getLanguageLogo(item.language)"
                                                :src="getLanguageLogo(item.language)" :alt="`${item.language} logo`"
                                                class="h-5 w-5 object-contain" />
                                            <Code2 v-else class="h-5 w-5" />
                                            {{ item.activity }}
                                            <span class="font-extralight capitalize">
                                                - {{ item.language }}
                                            </span>
                                        </ItemDescription>
                                    </ItemContent>
                                    <ItemContent class="relative">
                                        <div class="text-end space-y-1">
                                            <div class="flex items-center gap-1.5 justify-end">
                                                <Clock
                                                    :class="`size-3 ${getUrgencyColor(getDeadlineUrgency(item.expires_at))}`" />
                                                <ItemDescription class="text-xs font-medium">
                                                    {{ new Date(item.expires_at).toLocaleDateString() }}
                                                </ItemDescription>
                                            </div>
                                            <ItemDescription
                                                :class="`text-xs font-medium ${getUrgencyColor(getDeadlineUrgency(item.expires_at))}`">
                                                {{ (formatRelativeDeadline(item.expires_at)) }}
                                            </ItemDescription>
                                        </div>
                                    </ItemContent>
                                    </Link>
                                </Item>
                                <ItemSeparator v-if="index < upcomingThisWeek.data.length - 1" />
                            </template>

                            <template #loading>
                                <div class="flex justify-center items-center text-muted-foreground gap-1">
                                    <LoaderCircle class="animate-spin size-3" />
                                    <div class="text-xs">Loading items</div>
                                </div>
                            </template>
                        </InfiniteScroll>
                    </div>
                </ScrollArea>
            </ItemGroup>

            <div v-else class="flex items-center justify-center h-full text-muted-foreground text-sm">
                No due items this week
            </div>
        </div>
    </div>

    <Separator />

    <div class="text-left w-full">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2.5">
                <div class="p-1.5 rounded-md bg-destructive/10">
                    <Flag class="size-4 text-destructive" />
                </div>
                <h3 class="font-semibold text-sm">Flagged Detections</h3>
            </div>
            <Badge variant="secondary" class="text-xs font-medium px-2 py-0.5">
                {{ props.totalFlaggedDetections }}
            </Badge>
        </div>
        <div class="h-[205px]">
            <ItemGroup v-if="flaggedDetections.data && flaggedDetections.data.length > 0">
                <ScrollArea class="h-55 w-full">
                    <div class="p-1">
                        <InfiniteScroll data="flaggedDetections">
                            <template v-for="(item, index) in flaggedDetections.data" :key="item.id">
                                <Item>
                                    <ItemContent>
                                        <ItemTitle class="font-bold">
                                            {{ item.link_name }}
                                            <HoverCard>
                                                <HoverCardTrigger>
                                                    <div>
                                                        <span
                                                            class="font-extralight capitalize text-xs text-muted-foreground hover:underline">
                                                            - {{ getInitials(item.submitter_a) }} & {{
                                                                getInitials(item.submitter_b) }}
                                                            <Badge :variant="getSimilarityBadge(item.avg_score).variant"
                                                                :class="getSimilarityBadge(item.avg_score).class"
                                                                class="h-5 px-2 ml-1 rounded-full font-mono text-xs font-semibold tabular-nums border">
                                                                {{ formatScoreList(item.avg_score) }}%
                                                            </Badge>
                                                        </span>
                                                    </div>
                                                </HoverCardTrigger>
                                                <HoverCardContent class="w-80">
                                                    <div class="space-y-3">
                                                        <!-- Header with gradient -->
                                                        <div class="flex items-center justify-between pb-3 border-b">
                                                            <div>
                                                                <div class="inline-flex items-baseline gap-1">
                                                                    <div
                                                                        class="text-sm font-semibold text-foreground mb-1">
                                                                        Detection
                                                                    </div>
                                                                    <div class="text-xs text-muted-foreground">
                                                                        from:
                                                                    </div>
                                                                </div>
                                                                <div class="text-xs text-muted-foreground">
                                                                    {{ item.submitter_a }} & {{ item.submitter_b }}
                                                                </div>
                                                            </div>
                                                            <Badge :variant="getSimilarityBadge(item.avg_score).variant"
                                                                :class="getSimilarityBadge(item.avg_score).class"
                                                                class="text-md font-semibold">
                                                                {{ formatScore(item.avg_score) }}%
                                                            </Badge>
                                                        </div>

                                                        <div class="flex items-center gap-2 pt-2">
                                                            <div
                                                                class="relative flex-1 h-1.5 rounded-full overflow-hidden">
                                                                <div
                                                                    class="absolute inset-0 bg-gradient-to-r from-green-500 via-yellow-500 to-red-500">
                                                                </div>
                                                                <div class="absolute inset-0 bg-secondary transition-all"
                                                                    :style="{ marginLeft: `${item.avg_score * 100}%` }">
                                                                </div>
                                                            </div>
                                                            <span class="text-xs text-muted-foreground">Similarity
                                                                Score</span>
                                                        </div>
                                                    </div>
                                                </HoverCardContent>
                                            </HoverCard>
                                        </ItemTitle>
                                        <ItemDescription class="text-xs font-semibold">
                                            {{ item.activity }}
                                        </ItemDescription>
                                    </ItemContent>
                                    <ItemActions>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <AlertDialog>
                                                    <AlertDialogTrigger as-child>
                                                        <TooltipTrigger as-child>
                                                            <Button size="icon-sm" variant="ghost"
                                                                class="rounded-full hover:bg-destructive/10 hover:text-destructive">
                                                                <FlagOff />
                                                            </Button>
                                                        </TooltipTrigger>
                                                    </AlertDialogTrigger>

                                                    <AlertDialogContent>
                                                        <AlertDialogHeader>
                                                            <AlertDialogTitle class="text-destructive">
                                                                Are you absolutely sure?
                                                            </AlertDialogTitle>
                                                            <AlertDialogDescription>
                                                                Are you sure you want to remove this flag? The detection
                                                                will no longer appear in the flagged list.
                                                            </AlertDialogDescription>
                                                        </AlertDialogHeader>
                                                        <AlertDialogFooter>
                                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                            <Button variant="destructive"
                                                                @click.prevent="flagDetection(item)">
                                                                Remove Flag
                                                            </Button>
                                                        </AlertDialogFooter>
                                                    </AlertDialogContent>
                                                </AlertDialog>

                                                <TooltipContent>
                                                    Remove Flag
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        <ModalLink :href="`/detections/${item.id}`" position="top"
                                            #default="{ loading }">
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <Button size="icon-sm" variant="ghost"
                                                            class="rounded-full hover:bg-blue-500/10 hover:text-blue-500">
                                                            <template v-if="loading">
                                                                <LoaderCircle class="animate-spin" />
                                                            </template>
                                                            <template v-else>
                                                                <Eye />
                                                            </template>
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        View Comparison
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </ModalLink>
                                        <Link :href="`/activities/${item.activity_id}/links/${item.link_id}`">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button size="icon-sm" variant="ghost" class="rounded-full">
                                                        <ChevronRight />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    View {{ item.link_name }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        </Link>
                                    </ItemActions>
                                </Item>
                                <ItemSeparator v-if="index < flaggedDetections.data.length - 1" />
                            </template>

                            <template #loading>
                                <div class="flex justify-center items-center text-muted-foreground gap-1">
                                    <LoaderCircle class="animate-spin size-3" />
                                    <div class="text-xs">Loading items</div>
                                </div>
                            </template>
                        </InfiniteScroll>
                    </div>
                </ScrollArea>
            </ItemGroup>

            <div v-else class="flex items-center justify-center h-full text-muted-foreground text-sm">
                No flagged detections
            </div>
        </div>
    </div>
    <Separator />
</template>
