<script setup lang="ts">
import { VisAxis, VisGroupedBar, VisXYContainer } from "@unovis/vue"
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import { ChartContainer, ChartCrosshair, ChartTooltip, ChartTooltipContent, componentToString, ChartConfig } from "@/components/ui/chart"
import { Loader, FileQuestion } from "lucide-vue-next"
import SelectSeparator from "./ui/select/SelectSeparator.vue"
import { computed, ref, watch } from 'vue'
import type { AverageScorePerActivity, AverageScorePerActivityLink } from '@/types'

const emit = defineEmits<{ filterChanged: [filter: string] }>()
const props = defineProps<{ averageScorePerActivity: AverageScorePerActivity[] }>()
const selectedFilter = ref<string>('all')
const activityLinksData = ref<AverageScorePerActivityLink[]>([])
const isLoading = ref(false)

defineExpose({ selectedFilter })

const filterType = computed(() => {
    if (selectedFilter.value === 'all') return 'all'
    if (selectedFilter.value === 'java' || selectedFilter.value === 'python') return 'language'
    return 'activity'
})

const groupedActivities = computed(() => {
    const groups: Record<string, AverageScorePerActivity[]> = {}

    props.averageScorePerActivity.forEach(activity => {
        const language = activity.language || 'unknown'
        if (!groups[language]) {
            groups[language] = []
        }
        groups[language].push(activity)
    })

    return groups
})

const chartData = computed(() => {
    if (filterType.value === 'activity' && activityLinksData.value.length > 0) {
        return activityLinksData.value.map(item => ({
            activity: item.link_name,
            activityId: item.link_id,
            score: item.average_score * 100
        }))
    }

    if (filterType.value === 'language') {
        const language = selectedFilter.value
        const activities = groupedActivities.value[language] || []
        return activities.map(item => ({
            activity: item.activity_title,
            activityId: item.activity_id,
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
    if (filterType.value === 'activity') return 'Average Score by Activity Link'
    if (filterType.value === 'language') {
        const languageName = selectedFilter.value === 'java' ? 'Java' : 'Python'
        return `Average Score by ${languageName} Activity`
    }
    return 'Average Score by Activity'
})

const chartDescription = computed(() => {
    if (filterType.value === 'activity') return 'Overall Average by Submission Link'
    if (filterType.value === 'language') return `Activities filtered by ${selectedFilter.value}`
    return 'Overall Average by Activity'
})

const fetchActivityLinksData = async (activityId: string) => {
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
        }
        else {
            activityLinksData.value = []
        }
    }
    catch (error) {
        console.error('Error fetching activity links data:', error)
        activityLinksData.value = []
    }
    finally {
        isLoading.value = false
    }
}

watch(selectedFilter, (newValue) => {
    emit('filterChanged', newValue)

    activityLinksData.value = []

    if (filterType.value === 'activity') {
        fetchActivityLinksData(newValue)
    }
}, { immediate: true })

type Data = {
    activity: string
    activityId: string
    score: number
}

const chartConfig = {
    score: {
        label: "Avg. Score",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="cursor-default">{{ chartTitle }}</CardTitle>
            <CardDescription>{{ chartDescription }}</CardDescription>
            <CardAction>
                <Select v-model="selectedFilter">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter Activity" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Activities</SelectLabel>
                            <SelectItem value="all">
                                All
                            </SelectItem>
                            <SelectSeparator />
                        </SelectGroup>

                        <SelectGroup v-if="groupedActivities['java']?.length > 0">
                            <SelectLabel>Java</SelectLabel>
                            <SelectItem value="java">
                                All <span class="text-xs text-muted-foreground">(Java)</span>
                            </SelectItem>
                            <SelectItem v-for="item in groupedActivities['java']" :key="item.activity_id"
                                :value="item.activity_id">
                                {{ item.activity_title }}
                            </SelectItem>
                            <SelectSeparator />
                        </SelectGroup>

                        <!-- Python Activities -->
                        <SelectGroup v-if="groupedActivities['python']?.length > 0">
                            <SelectLabel>Python</SelectLabel>
                            <SelectItem value="python">
                                All <span class="text-xs text-muted-foreground">(Python)</span>
                            </SelectItem>
                            <SelectItem v-for="item in groupedActivities['python']" :key="item.activity_id"
                                :value="item.activity_id">
                                {{ item.activity_title }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </CardAction>
        </CardHeader>
        <CardContent class="h-120 px-2 pt-4 sm:px-6 sm:pt-6 pb-4">
            <div v-if="isLoading" class="flex items-center justify-center h-full">
                <div class="text-sm text-muted-foreground">
                    <Loader class="animate-spin" />
                </div>
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
