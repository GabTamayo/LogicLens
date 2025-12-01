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

const props = defineProps<{
    activeLinksData: ActiveLinksData
}>()

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
        <Card class="@container/card h-full">
            <CardHeader>
                <CardDescription class="font-semibold">Activity Overall</CardDescription>
                <CardTitle class="text-5xl md:text-6xl font-semibold tabular-nums tracking-wider">
                    30%
                </CardTitle>
                <CardAction>
                    <Badge variant="outline">
                        <TrendingUp />
                        +12.5%
                    </Badge>
                </CardAction>
            </CardHeader>
        </Card>

        <Card class="@container/card h-full">
            <CardHeader>
                <CardDescription class="font-semibold">Undetected Submissions</CardDescription>
                <CardTitle class="text-5xl md:text-6xl font-semibold tabular-nums tracking-wider">
                    6
                </CardTitle>
            </CardHeader>
        </Card>

        <Card class="@container/card h-full">
            <CardHeader>
                <CardDescription class="font-semibold">Total Activities</CardDescription>
                <CardTitle class="text-5xl md:text-6xl font-semibold tabular-nums tracking-wider">
                    21
                </CardTitle>
            </CardHeader>
        </Card>
    </div>
</template>
