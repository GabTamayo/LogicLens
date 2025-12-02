<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type DashboardPageProps } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import DashboardCards from '@/components/DashboardCards.vue';
import DashboardSidebar from '@/components/DashboardSidebar.vue';
import DashboardBarchart from '@/components/DashboardBarchart.vue';

// Define props to receive data from the controller
defineProps<DashboardPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 lg:p-6 flex flex-1 flex-col">
            <div class="@container/main flex flex-1 gap-4 lg:gap-6">

                <!-- Left/Main Content Area -->
                <div class="flex flex-1 flex-col gap-2">
                    <div class="w-full">
                        <!-- Pass the activeLinksData prop to DashboardCards -->
                        <DashboardCards :total-activity-links="totalActivityLinks"
                            :total-links-without-detections="totalLinksWithoutDetections"
                            :total-average-score="totalAverageScore" />
                    </div>
                    <div>
                        <DashboardBarchart :average-score-per-activity="averageScorePerActivity"
                            :average-score-per-activity-grouped-by-language="averageScorePerActivityGroupedByLanguage" />
                    </div>
                </div>

                <!-- Right Sidebar (Empty for now) -->
                <aside class="hidden xl:flex flex-col lg:w-80 xl:w-96 gap-4">
                    <DashboardSidebar :active-links-data="activeLinksData" :upcoming-this-week="upcomingThisWeek"
                        :total-upcoming-this-week="totalUpcomingThisWeek" :flagged-detections="flaggedDetections"
                        :total-flagged-detections="totalFlaggedDetections" />
                </aside>

            </div>
        </div>
    </AppLayout>
</template>
