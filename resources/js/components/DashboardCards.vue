<script setup lang="ts">
import { AlertTriangle, CheckCircle2, TrendingUp, } from "lucide-vue-next"
import { Badge } from "@/components/ui/badge"
import { Card, CardAction, CardDescription, CardHeader, CardTitle, CardContent, CardFooter } from "@/components/ui/card"
import { computed } from "vue";

const { totalActivityLinks, totalLinksWithoutDetections, totalAverageScore } = defineProps<{
    totalActivityLinks: number;
    totalLinksWithoutDetections: number;
    totalAverageScore: number;
}>();

const totalAverageScorePercentage = totalAverageScore
    ? Math.round(totalAverageScore * 100)
    : 0;

const scoreStatus = computed(() => {
    const score = totalAverageScore;
    if (score >= 0.90) {
        return {
            color: 'text-red-600 dark:text-red-400',
            bg: 'bg-red-50 dark:bg-red-950/30',
            label: 'Very High',
            variant: 'destructive' as const,
            icon: AlertTriangle
        };
    }
    if (score >= 0.85) {
        return {
            color: 'text-orange-600 dark:text-orange-400',
            bg: 'bg-orange-50 dark:bg-orange-950/30',
            label: 'High',
            variant: 'customOrange' as const,
            icon: AlertTriangle
        };
    }
    if (score >= 0.75) {
        return {
            color: 'text-yellow-600 dark:text-yellow-400',
            bg: 'bg-yellow-50 dark:bg-yellow-950/30',
            label: 'Moderate',
            variant: 'customYellow' as const,
            icon: TrendingUp
        };
    }
    return {
        color: 'text-green-600 dark:text-green-400',
        bg: 'bg-green-50 dark:bg-green-950/30',
        label: 'Low',
        variant: 'outline' as const,
        icon: CheckCircle2
    };
});

const detectionProgress = computed(() => {
    if (totalActivityLinks === 0) return 0;
    const withDetections = totalActivityLinks - totalLinksWithoutDetections;
    return Math.round((withDetections / totalActivityLinks) * 100);
});
</script>

<template>
    <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
        <Card class="@container/card h-full relative overflow-hidden transition-all hover:shadow-md">
            <div :class="[scoreStatus.bg, 'absolute inset-0 opacity-50']" />
            <CardHeader class="relative">
                <div class="flex items-start justify-between">
                    <CardDescription class="font-semibold text-muted-foreground">
                        Average Similarity Score
                    </CardDescription>
                    <component :is="scoreStatus.icon" :class="[scoreStatus.color, 'h-5 w-5']" />
                </div>

                <div class="flex items-baseline gap-2">
                    <CardTitle class="text-5xl md:text-6xl font-bold tabular-nums tracking-tight">
                        {{ totalAverageScorePercentage }}
                    </CardTitle>
                    <span :class="[scoreStatus.color, 'text-2xl font-semibold']">%</span>
                </div>

                <div class="pt-2">
                    <Badge :variant="scoreStatus.variant" class="font-medium">
                        {{ scoreStatus.label }}
                    </Badge>
                </div>
            </CardHeader>
        </Card>

        <Card class="@container/card h-full relative overflow-hidden transition-all hover:shadow-md">
            <CardHeader>
                <div class="flex items-start justify-between">
                    <CardDescription class="font-semibold text-muted-foreground">
                        Pending Detections
                    </CardDescription>
                    <AlertTriangle :class="[
                        totalLinksWithoutDetections > 0
                            ? 'text-amber-600 dark:text-amber-400'
                            : 'text-muted-foreground/30'
                    ]" class="h-5 w-5" />
                </div>

                <div class="flex items-baseline gap-2">
                    <CardTitle class="text-5xl md:text-6xl font-bold tabular-nums tracking-tight">
                        {{ totalLinksWithoutDetections }}
                    </CardTitle>
                    <span class="text-lg text-muted-foreground font-medium">
                        / {{ totalActivityLinks }}
                    </span>
                </div>

                <div class="pt-2 space-y-1.5">
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <span>Detection Progress</span>
                        <span class="font-medium">{{ detectionProgress }}%</span>
                    </div>
                    <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-500 ease-out rounded-full"
                            :style="{ width: `${detectionProgress}%` }" />
                    </div>
                </div>
            </CardHeader>
        </Card>

        <Card class="@container/card h-full relative overflow-hidden transition-all hover:shadow-md">
            <CardHeader>
                <CardDescription class="font-semibold">Total Submission Links</CardDescription>
                <CardTitle class="text-5xl md:text-6xl font-semibold tabular-nums tracking-wider">
                    {{ totalActivityLinks }}
                </CardTitle>
            </CardHeader>
        </Card>
    </div>
</template>
