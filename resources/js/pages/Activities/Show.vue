<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const page = usePage();
const activity = page.props;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: activity.title, href: `/activities/${activity.id}` },
];

</script>

<template>

    <Head title="Activity Name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
