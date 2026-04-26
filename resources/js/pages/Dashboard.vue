<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type DashboardPageProps } from '@/types';
import { Deferred, Head } from '@inertiajs/vue3';
import { Drawer, DrawerContent, DrawerDescription, DrawerHeader, DrawerTitle, DrawerTrigger, } from '@/components/ui/drawer'
import Button from '@/components/ui/button/Button.vue';
import DashboardCards from '@/components/DashboardCards.vue';
import DashboardSidebar from '@/components/DashboardSidebar.vue';
import DashboardBarchart from '@/components/DashboardBarchart.vue';
import { onUnmounted, ref, watch } from 'vue';
import { ChevronUp } from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';
import { Skeleton } from '@/components/ui/skeleton';

const props = defineProps<DashboardPageProps>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
const dynamicAverageScore = ref<number>(props.totalAverageScore ?? 0);
const isLoadingScore = ref(false);
const isDrawerOpen = ref(false);
const handleResize = () => {
    if (window.innerWidth >= 1280) {
        isDrawerOpen.value = false;
    }
};

window.addEventListener('resize', handleResize);
onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
watch(
    () => props.totalAverageScore,
    (value) => {
        if (value !== undefined && value !== null) {
            dynamicAverageScore.value = value;
        }
    },
);
const fetchAverageScore = async (filter: string) => {
    isLoadingScore.value = true;
    try {
        const response = await fetch(`/dashboard/average-score?filter=${encodeURIComponent(filter)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const result = await response.json();
            dynamicAverageScore.value = result.averageScore ?? props.totalAverageScore ?? 0;
        }
    } catch (error) {
        console.error('Error fetching average score:', error);
    } finally {
        isLoadingScore.value = false;
    }
};

const handleFilterChanged = (filter: string) => {
    fetchAverageScore(filter);
};
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 lg:p-6 flex flex-1 flex-col">
            <div class="@container/main flex flex-1 gap-4 lg:gap-6 min-w-0">

                <div class="flex flex-1 flex-col gap-2 min-w-0">
                    <div class="xl:hidden">
                        <Drawer v-model:open="isDrawerOpen">
                            <DrawerTrigger as-child>
                                <Button variant="outline" size="sm" class="w-full">
                                    <ChevronUp class="fill-current stroke-none text-muted-foreground" />
                                    <div class="text-muted-foreground">View Activity Details</div>
                                </Button>
                            </DrawerTrigger>
                            <DrawerContent class="h-[96vh] max-h-[96vh]">
                                <div class="w-full h-full flex flex-col">
                                    <DrawerHeader class="flex-shrink-0">
                                        <DrawerTitle>Activity Details</DrawerTitle>
                                        <DrawerDescription>View your active links, upcoming activities, and flagged
                                            detections.</DrawerDescription>
                                    </DrawerHeader>
                                    <div class="flex-1 overflow-y-auto px-4 pb-4">
                                        <div class="space-y-6 pt-4">
                                            <Deferred
                                                :data="['activeLinksData', 'totalUpcomingThisWeek', 'flaggedDetections', 'totalFlaggedDetections']">
                                                <template #fallback>
                                                    <div class="space-y-4">
                                                        <Skeleton class="h-44 w-full rounded-xl border" />
                                                        <Skeleton class="h-64 w-full rounded-xl border" />
                                                        <Skeleton class="h-56 w-full rounded-xl border" />
                                                    </div>
                                                </template>
                                                <DashboardSidebar :active-links-data="activeLinksData"
                                                    :upcoming-this-week="upcomingThisWeek"
                                                    :total-upcoming-this-week="totalUpcomingThisWeek"
                                                    :flagged-detections="flaggedDetections"
                                                    :total-flagged-detections="totalFlaggedDetections"
                                                    :close-drawer="() => isDrawerOpen = false" />
                                            </Deferred>
                                        </div>
                                    </div>
                                </div>
                            </DrawerContent>
                        </Drawer>
                    </div>

                    <div class="w-full">
                        <Deferred
                            :data="['totalActivityLinks', 'totalLinksWithoutDetections', 'totalAverageScore']">
                            <template #fallback>
                                <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
                                    <Skeleton class="h-48 w-full rounded-xl" />
                                    <Skeleton class="h-48 w-full rounded-xl" />
                                    <Skeleton class="h-48 w-full rounded-xl" />
                                </div>
                            </template>
                            <DashboardCards :total-activity-links="totalActivityLinks"
                                :total-links-without-detections="totalLinksWithoutDetections"
                                :total-average-score="dynamicAverageScore" />
                        </Deferred>
                    </div>
                    <div>
                        <Deferred data="averageScorePerActivity">
                            <template #fallback>
                                <Skeleton class="h-[580px] w-full rounded-xl" />
                            </template>
                            <DashboardBarchart @filter-changed="handleFilterChanged"
                                :average-score-per-activity="averageScorePerActivity" />
                        </Deferred>
                    </div>
                </div>

                <aside class="hidden xl:flex flex-col w-80 xl:w-96 min-w-80 shrink-0 gap-4">
                    <Deferred
                        :data="['activeLinksData', 'totalUpcomingThisWeek', 'flaggedDetections', 'totalFlaggedDetections']">
                        <template #fallback>
                            <div class="space-y-4">
                                <Skeleton class="h-52 w-full rounded-xl" />
                                <Skeleton class="h-66 w-full rounded-xl" />
                                <Skeleton class="h-66 w-full rounded-xl" />
                            </div>
                        </template>
                        <DashboardSidebar :active-links-data="activeLinksData" :upcoming-this-week="upcomingThisWeek"
                            :total-upcoming-this-week="totalUpcomingThisWeek" :flagged-detections="flaggedDetections"
                            :total-flagged-detections="totalFlaggedDetections" />
                    </Deferred>
                </aside>

            </div>
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
