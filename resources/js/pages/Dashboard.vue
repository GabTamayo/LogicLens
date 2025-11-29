<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type DashboardPageProps } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import DashboardCards from '@/components/DashboardCards.vue';
import DashboardSidebar from '@/components/DashboardSidebar.vue';

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
                        <DashboardCards :active-links-data="activeLinksData" />
                    </div>
                    <div
                        class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                        <PlaceholderPattern />
                    </div>
                </div>

                <!-- Right Sidebar (Empty for now) -->
                <aside class="hidden xl:flex flex-col lg:w-80 xl:w-96 gap-4">
                    <DashboardSidebar />
                </aside>

            </div>
        </div>
    </AppLayout>
</template>
