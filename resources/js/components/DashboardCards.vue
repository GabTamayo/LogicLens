<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSimilarity } from '@/composables/useSimilarity';
import { ModalLink } from '@inertiaui/modal-vue';
import { AlertTriangle, LoaderCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const { totalActivityLinks, totalLinksWithoutDetections, totalAverageScore } = defineProps<{
    totalActivityLinks: number;
    totalLinksWithoutDetections: number;
    totalAverageScore: number | null;
}>();

const animatedScore = ref(totalAverageScore ? Math.round(totalAverageScore * 100) : 0);

watch(
    () => totalAverageScore,
    (newScore) => {
        const targetScore = newScore ? Math.round(newScore * 100) : 0;
        const startScore = animatedScore.value;
        const duration = 800;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);

            const easeOutQuad = (t: number) => t * (2 - t);
            const easedProgress = easeOutQuad(progress);

            animatedScore.value = Math.round(startScore + (targetScore - startScore) * easedProgress);

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    },
    { immediate: false },
);

const { getSimilarityBadge } = useSimilarity();
const scoreStatus = computed(() => {
    if (totalAverageScore === null) {
        return getSimilarityBadge(0);
    }
    return getSimilarityBadge(totalAverageScore);
});

const detectionProgress = computed(() => {
    if (totalActivityLinks === 0) return 0;
    const withDetections = totalActivityLinks - totalLinksWithoutDetections;
    return Math.round((withDetections / totalActivityLinks) * 100);
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <Card class="@container/card relative h-full overflow-hidden transition-all hover:shadow-md">
            <div :class="[scoreStatus.bg, 'absolute inset-0 opacity-50']" />

            <CardHeader class="relative">
                <div class="flex items-start justify-between">
                    <CardDescription class="font-semibold text-muted-foreground"> Overall Similarity Score </CardDescription>

                    <component :is="scoreStatus.icon" :class="[scoreStatus.class, 'h-5 w-5']" />
                </div>

                <div class="flex items-baseline gap-2">
                    <CardTitle class="text-5xl font-bold tracking-tight tabular-nums md:text-6xl">
                        {{ animatedScore }}
                    </CardTitle>

                    <span :class="[scoreStatus.class, 'text-2xl font-semibold']">%</span>
                </div>

                <div class="pt-2">
                    <Badge :variant="scoreStatus.variant" class="font-medium">
                        {{ scoreStatus.label }}
                    </Badge>
                </div>
            </CardHeader>
        </Card>

        <ModalLink href="dashboard/pending-detections" #default="{ loading }">
            <Card class="@container/card relative h-full overflow-hidden transition-all hover:shadow-md">
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <CardDescription class="font-semibold text-muted-foreground"> Pending Detections </CardDescription>
                        <template v-if="loading">
                            <LoaderCircle class="h-5 w-5 animate-spin text-muted-foreground" />
                        </template>
                        <template v-else>
                            <AlertTriangle
                                :class="[totalLinksWithoutDetections > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-muted-foreground/30']"
                                class="h-5 w-5"
                            />
                        </template>
                    </div>

                    <div class="flex items-baseline gap-2">
                        <CardTitle class="text-5xl font-bold tracking-tight tabular-nums md:text-6xl">
                            {{ totalLinksWithoutDetections }}
                        </CardTitle>
                        <span class="text-lg font-medium text-muted-foreground"> / {{ totalActivityLinks }} </span>
                    </div>

                    <div class="space-y-1.5 pt-2">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span>Detection Progress</span>
                            <span class="font-medium">{{ detectionProgress }}%</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-primary transition-all duration-500 ease-out"
                                :style="{ width: `${detectionProgress}%` }"
                            />
                        </div>
                    </div>
                </CardHeader>
            </Card>
        </ModalLink>

        <Card class="@container/card relative h-full overflow-hidden transition-all hover:shadow-md">
            <CardHeader>
                <CardDescription class="font-semibold">Total Submission Links</CardDescription>
                <CardTitle class="text-5xl font-semibold tracking-wider tabular-nums md:text-6xl">
                    {{ totalActivityLinks }}
                </CardTitle>
            </CardHeader>
        </Card>
    </div>
</template>
