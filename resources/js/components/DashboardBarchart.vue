<script setup lang="ts">
import { VisAxis, VisGroupedBar, VisXYContainer } from "@unovis/vue"
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import { ChartContainer, ChartCrosshair, ChartTooltip, ChartTooltipContent, componentToString, ChartConfig, ChartLegendContent } from "@/components/ui/chart"
import { Loader } from "lucide-vue-next"
import SelectSeparator from "./ui/select/SelectSeparator.vue"
import { computed, ref, watch } from 'vue'
import type { AverageScorePerActivity, AverageScorePerActivityLink } from '@/types'

const props = defineProps<{
    averageScorePerActivity: AverageScorePerActivity[]
}>()

const selectedActivityId = ref<string>('all')
const activityLinksData = ref<AverageScorePerActivityLink[]>([])
const isLoading = ref(false)
const isFiltered = computed(() => selectedActivityId.value !== 'all')

const chartData = computed(() => {
    if (isFiltered.value && activityLinksData.value.length > 0) {
        return activityLinksData.value.map(item => ({
            activity: item.link_name,
            activityId: item.link_id,
            score: item.average_score * 100
        }))
    }

    return props.averageScorePerActivity.map(item => ({
        activity: item.activity_title,
        activityId: item.activity_id,
        score: item.average_score * 100
    }))
})

const chartTitle = computed(() => {
    return isFiltered.value ? 'Average Score by Activity Link' : 'Average Score by Activity'
})

const chartDescription = computed(() => {
    return isFiltered.value ? 'Overall Average by Submission Link' : 'Overall Average by Activity'
})

const fetchActivityLinksData = async (activityId: string) => {
    if (activityId === 'all') {
        activityLinksData.value = []
        return
    }

    isLoading.value = true
    try {
        const response = await fetch(`/dashboard/average-score-per-activity-link/${activityId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        if (response.ok) {
            const result = await response.json()
            activityLinksData.value = result.data || []
        } else {
            activityLinksData.value = []
        }
    } catch (error) {
        console.error('Error fetching activity links data:', error)
        activityLinksData.value = []
    } finally {
        isLoading.value = false
    }
}

watch(selectedActivityId, (newValue) => {
    fetchActivityLinksData(newValue)
})

type Data = {
    activity: string
    activityId: string
    score: number
}

const chartConfig = {
    score: {
        label: "Avg. Score",
        color: "var(--chart-2)",
    },
} satisfies ChartConfig
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ chartTitle }}</CardTitle>
            <CardDescription>{{ chartDescription }}</CardDescription>
            <CardAction>
                <Select v-model="selectedActivityId">
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
                            <SelectItem v-for="item in averageScorePerActivity" :key="item.activity_id"
                                :value="item.activity_id">
                                {{ item.activity_title }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </CardAction>
        </CardHeader>
        <CardContent class="h-120">
            <div v-if="isLoading" class="flex items-center justify-center h-full">
                <div class="text-sm text-muted-foreground">
                    <Loader class="animate-spin" />
                </div>
            </div>
            <div v-else-if="isFiltered && activityLinksData.length === 0"
                class="flex items-center justify-center h-full">
                <div class="text-sm text-muted-foreground">No data available for this activity</div>
            </div>
            <ChartContainer v-else :config="chartConfig">
                <VisXYContainer :data="chartData" :margin="{ left: 0 }" :y-domain="[0, 100]">
                    <VisGroupedBar :x="(d: Data, i: number) => i" :y="(d: Data) => d.score"
                        :color="chartConfig.score.color" :rounded-corners="25" />
                    <VisAxis type="x" :x="(d: Data, i: number) => i" :tick-line="false" :domain-line="false"
                        :grid-line="false" :num-ticks="chartData.length" :tick-format="(d: number) => {
                            const item = chartData[d]
                            return item ? item.activity : ''
                        }" />
                    <VisAxis type="y" :num-ticks="8" :tick-line="false" :domain-line="false"
                        :tick-format="(d: number) => `${d}%`" />
                    <ChartTooltip />
                    <ChartCrosshair :template="componentToString(chartConfig, ChartTooltipContent, {
                        labelKey: 'false',
                        nameKey: 'activity',
                    })" color="#0000" />
                </VisXYContainer>
            </ChartContainer>
        </CardContent>
    </Card>
</template>
