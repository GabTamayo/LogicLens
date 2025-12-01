<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { TrendingDown, TrendingUp, Circle, FileScan, Flag, SquareArrowOutUpRight, Eye, FlagOff, Calendar, ChevronRight, LoaderCircle } from "lucide-vue-next"
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
import { ActiveLinksData, FlaggedDetections, UpcomingThisWeek } from '@/types';
import { formatDistanceToNow, parseISO } from 'date-fns';
import { router, Link, Deferred } from '@inertiajs/vue3';

const props = defineProps<{
    activeLinksData: ActiveLinksData;
    upcomingThisWeek: UpcomingThisWeek[];
    totalUpcomingThisWeek: number;
    flaggedDetections: FlaggedDetections[];
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
</script>

<template>
    <ModalLink href="dashboard/active-links" #default="{ loading }"
        class="focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded-lg">
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
                <h3 class="font-semibold text-sm">Upcoming This Week</h3>
            </div>
            <Badge variant="secondary" class="text-xs font-medium px-2 py-0.5">
                {{ props.totalUpcomingThisWeek }}
            </Badge>
        </div>
        <Deferred data="upcomingThisWeek">
            <template #fallback>
                <div class="h-[205px] flex flex-col gap-2">
                    <Skeleton v-for="i in 3" :key="i" class="h-15 w-full" />
                </div>
            </template>

            <div class="h-[205px]">
                <ItemGroup v-if="upcomingThisWeek.length > 0">
                    <ScrollArea class="h-55 w-full rounded-md">
                        <div class="p-1">
                            <template v-for="(item, index) in upcomingThisWeek" :key="item.id">
                                <Item role="listitem" as-child>
                                    <Link :href="`/activities/${item.activity_id}`">
                                    <ItemContent>
                                        <ItemTitle class="text-sm font-bold">
                                            {{ item.name }}
                                        </ItemTitle>
                                        <ItemDescription class="text-xs font-semibold">
                                            {{ item.activity }}
                                            <span class="font-extralight capitalize">
                                                - {{ item.language }}
                                            </span>
                                        </ItemDescription>
                                    </ItemContent>
                                    <ItemContent>
                                        <div class="text-end">
                                            <ItemDescription class="text-xs">
                                                {{ new Date(item.expires_at).toLocaleDateString() }}
                                            </ItemDescription>
                                            <ItemDescription class="text-xs">
                                                {{ (formatRelativeDeadline(item.expires_at)) }}
                                            </ItemDescription>
                                        </div>
                                    </ItemContent>
                                    </Link>
                                </Item>
                                <ItemSeparator v-if="index < upcomingThisWeek.length - 1" />
                            </template>
                        </div>
                    </ScrollArea>
                </ItemGroup>

                <div v-else class="flex items-center justify-center h-full text-muted-foreground text-sm">
                    No upcoming items this week
                </div>
            </div>
        </Deferred>
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
        <Deferred data="flaggedDetections">
            <template #fallback>
                <div class="h-[205px] flex flex-col gap-2">
                    <Skeleton v-for="i in 3" :key="i" class="h-15 w-full" />
                </div>
            </template>

            <div class="h-[205px]">
                <ItemGroup v-if="flaggedDetections.length > 0">
                    <ScrollArea class="h-55 w-full">
                        <div class="p-1">
                            <template v-for="(item, index) in flaggedDetections" :key="item.id">
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
                                                            <Badge variant="secondary"
                                                                class="ml-2 h-5 rounded-full font-mono tabular-nums">{{
                                                                    formatScoreList(item.avg_score) }}%</Badge>
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
                                                            <Badge variant="outline"
                                                                class="text-lg px-3 py-1 font-semibold">
                                                                {{ formatScore(item.avg_score) }} %
                                                            </Badge>
                                                        </div>

                                                        <div class="flex items-center gap-2 pt-2">
                                                            <div
                                                                class="relative flex-1 h-2 rounded-full overflow-hidden">
                                                                <div
                                                                    class="absolute inset-0 bg-gradient-to-r from-green-500 via-yellow-500 to-red-500">
                                                                </div>
                                                                <div class="absolute inset-0 bg-secondary transition-all"
                                                                    :style="{ marginLeft: `${item.avg_score * 100}%` }">
                                                                </div>
                                                            </div>
                                                            <span class="text-xs text-muted-foreground">Score</span>
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
                                                                Are you sure you want to remove the flag? This action
                                                                cannot be undone.
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
                                <ItemSeparator v-if="index < flaggedDetections.length - 1" />
                            </template>
                        </div>
                    </ScrollArea>
                </ItemGroup>

                <div v-else class="flex items-center justify-center h-full text-muted-foreground text-sm">
                    No flagged detections
                </div>
            </div>
        </Deferred>
    </div>
    <Separator />
</template>
