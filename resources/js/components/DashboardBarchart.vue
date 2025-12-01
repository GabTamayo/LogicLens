<script setup lang="ts">
import type { ChartConfig, } from "@/components/ui/chart"

import { VisAxis, VisGroupedBar, VisXYContainer } from "@unovis/vue"
import { TrendingUp } from "lucide-vue-next"
import { Card, CardAction, CardContent, CardDescription, CardFooter, CardHeader, CardTitle, } from "@/components/ui/card"
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import { ChartContainer, ChartCrosshair, ChartTooltip, ChartTooltipContent, componentToString, } from "@/components/ui/chart"
import SelectSeparator from "./ui/select/SelectSeparator.vue"

const chartData = [
    { date: new Date("2024-01-01"), desktop: 80.20 },
    { date: new Date("2024-02-01"), desktop: 74.21 },
    { date: new Date("2024-03-01"), desktop: 92.30 },
    { date: new Date("2024-04-01"), desktop: 33.33 },
    { date: new Date("2024-05-01"), desktop: 40.28 },
    { date: new Date("2024-06-01"), desktop: 80.20 },
]

type Data = typeof chartData[number]

const chartConfig = {
    desktop: {
        label: "Activity",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Bar Chart</CardTitle>
            <CardDescription>Activities Overview</CardDescription>
            <CardAction>
                <Select>
                    <SelectTrigger class="w-[140px]">
                        <SelectValue placeholder="Filter Activity" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Activities</SelectLabel>
                            <SelectItem value="all">
                                All
                            </SelectItem>
                            <SelectSeparator />
                            <SelectItem value="apple">
                                Apple
                            </SelectItem>
                            <SelectItem value="banana">
                                Banana
                            </SelectItem>
                            <SelectItem value="blueberry">
                                Blueberry
                            </SelectItem>
                            <SelectItem value="grapes">
                                Grapes
                            </SelectItem>
                            <SelectItem value="pineapple">
                                Pineapple
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </CardAction>
        </CardHeader>
        <CardContent class="h-115">
            <ChartContainer :config="chartConfig">
                <VisXYContainer :data="chartData" :margin="{ left: -24 }" :y-domain="[0, undefined]">
                    <VisGroupedBar :x="(d: Data) => d.date" :y="(d: Data) => d.desktop"
                        :color="chartConfig.desktop.color" :rounded-corners="10" />
                    <VisAxis type="x" :x="(d: Data) => d.date" :tick-line="false" :domain-line="false"
                        :grid-line="false" :num-ticks="6" :tick-format="(d: number) => {
                            const date = new Date(d)
                            return date.toLocaleDateString('en-US', {
                                month: 'short',
                            })
                        }" :tick-values="chartData.map(d => d.date)" />
                    <VisAxis type="y" :num-ticks="3" :tick-line="false" :domain-line="false" />
                    <ChartTooltip />
                    <ChartCrosshair :template="componentToString(chartConfig, ChartTooltipContent, { hideLabel: true })"
                        color="#0000" />
                </VisXYContainer>
            </ChartContainer>
        </CardContent>
        <CardFooter class="flex-col items-start gap-2 text-sm">
            <div class="flex gap-2 font-medium leading-none">
                Trending up by 5.2% this month
                <TrendingUp class="h-4 w-4" />
            </div>
            <div class="leading-none text-muted-foreground">
                Showing total visitors for the last 6 months
            </div>
        </CardFooter>
    </Card>
</template>
