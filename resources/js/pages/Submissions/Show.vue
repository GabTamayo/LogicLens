<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Deferred } from '@inertiajs/vue3'
import { Head, router, useRemember } from '@inertiajs/vue3'
import { type BreadcrumbItem, type DetectionPageProps } from '@/types'
import DataTable from '@/components/DataTable.vue'
import { columns } from '@/components/detections/columns'
import { useDebounceFn } from '@vueuse/core'
import { LoaderCircle } from 'lucide-vue-next'
import { Alert, AlertDescription } from "@/components/ui/alert"
import { AlertCircle } from 'lucide-vue-next'

const props = defineProps<DetectionPageProps>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.name, href: `/activities/${props.activityId}/links/${props.link.id}` },
]

const filters = useRemember({
    student_name_a: props.filters?.student_name_a || '',
    student_name_b: props.filters?.student_name_b || '',
    min_score: props.filters?.min_score || '',
}, 'detection-filters')

const handlePageChange = (page: number) => {
    router.visit(props.detections.path, {
        data: {
            ...filters.value,
            page: page
        },
        preserveScroll: true,
        preserveState: true,
        only: ['detections'],
    })
}

const handleFilterChange = useDebounceFn(() => {
    router.visit(props.detections.path, {
        data: filters.value,
        preserveScroll: true,
        preserveState: true,
        only: ['detections'],
    })
}, 300)

const updateFilter = (column: string, value: string) => {
    filters.value[column] = value
    handleFilterChange()
}
</script>

<template>

    <Head :title="`Detection Results - ${props.link.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <h2 class="scroll-m-20 text-3xl font-semibold tracking-tight transition-colors first:mt-0">
                    Detection Summary
                </h2>
                <div class="flex items-center">
                    <p class="text-sm text-muted-foreground">{{ props.activityTitle }}</p>
                    <span v-if="props.activityDate" class="ml-2 text-xs text-muted-foreground">
                        ({{ props.activityDate }})
                    </span>
                </div>
            </div>

            <Deferred data="detections">
                <template #fallback>
                    <div class="flex items-center justify-center gap-2 h-64 border rounded-md">
                        <span class="text-muted-foreground">Loading detection results</span>
                        <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                    </div>
                </template>
                <DataTable :columns="columns" :data="props.detections.data" :pagination="props.detections"
                    :filter-config="[
                        { column: 'student_name_a', placeholder: 'Filter by Student A Name' },
                        { column: 'student_name_b', placeholder: 'Filter by Student B Name' },
                        { column: 'min_score', placeholder: 'Min Score (e.g., 0.8)' }
                    ]" :filter-values="filters" @page-change="handlePageChange" @filter-change="updateFilter"
                    :show-detect-button="false" />
            </Deferred>
        </div>
    </AppLayout>
</template>
