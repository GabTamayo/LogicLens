<script setup lang="ts">
import { TrendingDown, TrendingUp, Circle, FileScan, Flag, SquareArrowOutUpRight, Eye, FlagOff } from "lucide-vue-next"
import { Badge } from "@/components/ui/badge"
import { Card, CardAction, CardDescription, CardFooter, CardHeader, CardTitle, CardContent } from "@/components/ui/card"
import { ChartConfig, ChartContainer, ChartTooltip, ChartTooltipContent, componentToString, } from "@/components/ui/chart"
import { Donut } from "@unovis/ts"
import { VisDonut, VisSingleContainer } from "@unovis/vue"
import { Item, ItemContent, ItemDescription, ItemFooter, ItemHeader, ItemMedia, ItemTitle, ItemGroup, ItemSeparator } from '@/components/ui/item'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/components/ui/tooltip'
import { ModalLink } from '@inertiaui/modal-vue'
import { Separator } from "@/components/ui/separator"
import ScrollArea from "./ui/scroll-area/ScrollArea.vue"
import ItemActions from "./ui/item/ItemActions.vue"
import Button from "./ui/button/Button.vue"
import { computed } from "vue"
import type { ActiveLinksData } from "@/types"

// Only accept what we need
const props = defineProps<{
    activeLinksData: ActiveLinksData
}>()

const flaggedDetections = [
    { id: 1, course: 'BSIT-1', activity: 'Kazuya Mishima & Capstone Master', percent: '100%' },
    { id: 2, course: 'BSIT-2', activity: 'Kazuya Mishima & Capstone Master', percent: '76%' },
    { id: 3, course: 'BSIT-3', activity: 'Kazuya Mishima & Capstone Master', percent: '100%' },
    { id: 4, course: 'BSIT-1', activity: 'Kazuya Mishima & Capstone Master', percent: '76%' },
    { id: 5, course: 'BSIT-4', activity: 'Kazuya Mishima & Capstone Master', percent: '89%' },
    { id: 6, course: 'BSIT-2', activity: 'Kazuya Mishima & Capstone Master', percent: '92%' },
]

// Active Links Chart Data
const activeLinksChartData = computed(() => [
    { label: "No Deadline", value: props.activeLinksData?.noDeadline ?? 0, fill: "var(--chart-1)" },
    { label: "With Deadline", value: props.activeLinksData?.withDeadline ?? 0, fill: "var(--chart-2)" },
]);

const activeLinksChartConfig: ChartConfig = {
    value: { label: "Links", color: undefined },
    "With Deadline": { label: "With Deadline", color: "var(--chart-2)" },
    "No Deadline": { label: "No Deadline", color: "var(--chart-1)" },
};
</script>

<template>
    <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
        <!-- Active Links Card -->
        <ModalLink href="dashboard/active-links" #default="{ loading }"
            class="focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded-lg">
            <Card class="@container/card h-full transition-all hover:shadow-lg hover:border-primary/50 cursor-pointer">
                <CardHeader>
                    <CardDescription class="inline-flex items-center space-x-2">
                        <Circle v-if="loading" class="size-3 text-green-600 animate-caret-blink" :stroke="'none'"
                            :fill="'currentColor'" />
                        <Circle v-else class="size-3 text-green-600" :stroke="'none'" :fill="'currentColor'" />
                        <span class="font-semibold">Active Links</span>
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="activeLinksChartConfig" class="mx-auto aspect-square max-h-[250px]" :style="{
                        '--vis-donut-central-label-font-size': 'var(--text-3xl)',
                        '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
                        '--vis-donut-central-label-text-color': 'var(--foreground)',
                        '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
                    }">
                        <VisSingleContainer :data="activeLinksChartData" :margin="{ top: 30, bottom: 30 }">
                            <VisDonut :value="d => d.value" :color="d => activeLinksChartConfig[d.label].color"
                                :arc-width="30" :central-label="props.activeLinksData.total.toLocaleString()"
                                central-sub-label="Links" />
                            <ChartTooltip :triggers="{
                                [Donut.selectors.segment]: componentToString(activeLinksChartConfig, ChartTooltipContent, { hideLabel: true })!,
                            }" />
                        </VisSingleContainer>
                    </ChartContainer>
                </CardContent>
            </Card>
        </ModalLink>

        <Card class="@container/card h-full">
            <CardHeader>
                <CardDescription class="font-semibold">Not Detected Links Yet</CardDescription>
                <CardTitle class="text-5xl md:text-6xl font-semibold tabular-nums tracking-wider">
                    4
                </CardTitle>
            </CardHeader>
        </Card>

        <Card class="@container/card h-full">
            <CardHeader>
                <CardDescription class="inline-flex items-center space-x-2">
                    <Flag class="size-3 text-red-600" :fill="'currentColor'" aria-hidden="true" />
                    <span>Flagged Detections</span>
                </CardDescription>
                <CardTitle class="text-5xl md:text-6xl font-semibold tabular-nums tracking-wider">
                    {{ flaggedDetections.length }}
                </CardTitle>

                <CardAction>
                    <Select>
                        <SelectTrigger class="w-[180px]">
                            <SelectValue placeholder="Filter Detections" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Filter by</SelectLabel>
                                <SelectItem value="all">All</SelectItem>
                                <SelectItem value="day">This Day</SelectItem>
                                <SelectItem value="week">This Week</SelectItem>
                                <SelectItem value="month">This Month</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </CardAction>
            </CardHeader>

            <Separator />

            <CardContent>
                <ItemGroup v-if="flaggedDetections.length > 0">
                    <ScrollArea class="h-[280px] w-full">
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
                                                        <SquareArrowOutUpRight />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    View Detection List
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

                <div v-else class="text-center py-8 text-muted-foreground text-sm">
                    No flagged detections
                </div>
            </CardContent>
        </Card>
    </div>
</template>
