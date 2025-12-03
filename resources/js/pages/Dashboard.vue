<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type DashboardPageProps } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle, DrawerTrigger, } from '@/components/ui/drawer'
import Button from '@/components/ui/button/Button.vue';
import DashboardCards from '@/components/DashboardCards.vue';
import DashboardSidebar from '@/components/DashboardSidebar.vue';
import DashboardBarchart from '@/components/DashboardBarchart.vue';
import { onUnmounted, ref } from 'vue';
import { ChevronUp } from 'lucide-vue-next';

const props = defineProps<DashboardPageProps>();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
const dynamicAverageScore = ref<number>(props.totalAverageScore);
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
                                            <DashboardSidebar :active-links-data="activeLinksData"
                                                :upcoming-this-week="upcomingThisWeek"
                                                :total-upcoming-this-week="totalUpcomingThisWeek"
                                                :flagged-detections="flaggedDetections"
                                                :total-flagged-detections="totalFlaggedDetections"
                                                :close-drawer="() => isDrawerOpen = false" />
                                        </div>
                                    </div>
                                </div>
                            </DrawerContent>
                        </Drawer>
                    </div>

                    <div class="w-full">
                        <DashboardCards :total-activity-links="totalActivityLinks"
                            :total-links-without-detections="totalLinksWithoutDetections"
                            :total-average-score="dynamicAverageScore" />
                    </div>
                    <div>
                        <DashboardBarchart @filter-changed="handleFilterChanged"
                            :average-score-per-activity="averageScorePerActivity" />
                    </div>
                </div>

                <aside class="hidden xl:flex flex-col w-80 xl:w-96 min-w-80 shrink-0 gap-4">
                    <DashboardSidebar :active-links-data="activeLinksData" :upcoming-this-week="upcomingThisWeek"
                        :total-upcoming-this-week="totalUpcomingThisWeek" :flagged-detections="flaggedDetections"
                        :total-flagged-detections="totalFlaggedDetections" />
                </aside>

            </div>
        </div>
    </AppLayout>
</template>
