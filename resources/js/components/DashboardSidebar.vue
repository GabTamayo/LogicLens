<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { TrendingDown, TrendingUp, Circle, FileScan, Flag, SquareArrowOutUpRight, Eye, FlagOff, Calendar, ChevronRight } from "lucide-vue-next"
import { Separator } from '@/components/ui/separator';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { Item, ItemContent, ItemDescription, ItemFooter, ItemHeader, ItemMedia, ItemTitle, ItemGroup, ItemSeparator, ItemActions } from '@/components/ui/item'
import { ChartConfig, ChartContainer, ChartTooltip, ChartTooltipContent, componentToString, } from "@/components/ui/chart"
import { Donut } from "@unovis/ts"
import { VisDonut, VisSingleContainer } from "@unovis/vue"
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, SelectSeparator } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/components/ui/tooltip'
import Button from "./ui/button/Button.vue"
import Badge from './ui/badge/Badge.vue';
import { ModalLink } from '@inertiaui/modal-vue'
import { ActiveLinksData } from '@/types';

const props = defineProps<{
    activeLinksData: ActiveLinksData
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

const flaggedDetections = [
    { id: 1, course: 'BSIT-1', activity: 'Kazuya Mishima & Capstone Master', percent: '100%' },
    { id: 2, course: 'BSIT-2', activity: 'Kazuya Mishima & Capstone Master', percent: '76%' },
    { id: 3, course: 'BSIT-3', activity: 'Kazuya Mishima & Capstone Master', percent: '100%' },
    { id: 4, course: 'BSIT-1', activity: 'Kazuya Mishima & Capstone Master', percent: '76%' },
    { id: 5, course: 'BSIT-4', activity: 'Kazuya Mishima & Capstone Master', percent: '89%' },
    { id: 6, course: 'BSIT-2', activity: 'Kazuya Mishima & Capstone Master', percent: '92%' },
]
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
                    :central-sub-label="loading ? 'Links...' : 'Links'" />
                <ChartTooltip :triggers="{
                    [Donut.selectors.segment]: componentToString(activeLinksChartConfig, ChartTooltipContent, { hideLabel: true })!,
                }" />
            </VisSingleContainer>
        </ChartContainer>
    </ModalLink>

    <Separator class="mt-2" />

    <div class="text-left w-full">
        <div class="flex justify-between items-center mb-2">
            <div class="inline-flex gap-2 items-center text-muted-foreground">
                <p class="font-medium">Upcoming This Week</p>
                <Calendar class="size-3" />
            </div>
            <div class="text-xs flex items-end text-muted-foreground m-2">
                <p>4 items</p>
            </div>
        </div>
        <div class="h-[220px]">
            <ItemGroup v-if="flaggedDetections.length > 0">
                <ScrollArea class="h-55 w-full rounded-md">
                    <div class="p-1">
                        <template v-for="(item, index) in flaggedDetections" :key="item.id">
                            <Item role="listitem" as-child>
                                <a href="#">
                                    <ItemContent>
                                        <ItemTitle class="text-sm font-medium">
                                            {{ item.course }}
                                        </ItemTitle>
                                        <ItemDescription class="text-xs">
                                            {{ item.activity }}
                                        </ItemDescription>
                                    </ItemContent>
                                    <ItemContent>
                                        <ItemDescription class="text-xs">In about 4 days</ItemDescription>
                                    </ItemContent>
                                </a>
                            </Item>
                            <ItemSeparator v-if="index < flaggedDetections.length - 1" />
                        </template>
                    </div>
                </ScrollArea>
            </ItemGroup>

            <div v-else class="flex items-center justify-center h-full text-muted-foreground text-sm">
                No items due this week
            </div>
        </div>
    </div>

    <div class="text-left w-full">
        <div class="flex justify-between items-center mb-2">
            <div class="inline-flex gap-2 items-center text-muted-foreground">
                <p class="font-medium">Flagged Detections</p>
                <Flag class="size-3" />
            </div>
            <div class="text-xs flex text-end text-muted-foreground">
                <p>6 items</p>
            </div>
        </div>
        <div class="h-[270px]">
            <ItemGroup v-if="flaggedDetections.length > 0">
                <ScrollArea class="h-[260px] w-full">
                    <div class="p-1">
                        <template v-for="(item, index) in flaggedDetections" :key="item.id">
                            <Item>
                                <ItemContent>
                                    <ItemTitle class="flex justify-between items-center">
                                        <div>
                                            {{ item.course }}
                                            <span class="text-muted-foreground">(Activity 1)</span>
                                        </div>
                                        <Badge>{{ item.percent }}</Badge>
                                    </ItemTitle>
                                    <ItemDescription>
                                        {{ item.activity }}
                                    </ItemDescription>
                                </ItemContent>
                                <ItemActions>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button size="icon-sm" variant="outline" class="rounded-full">
                                                    <FlagOff class="text-destructive" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                Remove Flag
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button size="icon-sm" variant="outline" class="rounded-full">
                                                    <Eye />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                View Comparison
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button size="icon-sm" variant="outline" class="rounded-full">
                                                    <ChevronRight />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                View Activity 1 List
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </ItemActions>
                            </Item>
                            <ItemSeparator v-if="index < flaggedDetections.length - 1" />
                        </template>
                    </div>
                </ScrollArea>
            </ItemGroup>

            <div v-else class="flex items-center justify-center h-full text-muted-foreground text-sm">
                No items due this week
            </div>
        </div>
    </div>
</template>
