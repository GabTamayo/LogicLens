<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem, Submission } from '@/types'
import AlertDialogDelete from '@/components/AlertDialogDelete.vue'
import DataTable from '@/components/DataTable.vue'
import { columns } from '@/components/submissions/columns'
import { ref } from 'vue'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps<Submission & { filters: Record<string, string> }>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.name, href: `/activities/${props.activityId}/links/${props.link.id}` }
]

const filters = ref({
    student_name: props.filters?.student_name || '',
    student_no: props.filters?.student_no || '',
})

const handlePageChange = (page: number) => {
    router.visit(props.submissions.path, {
        data: {
            ...filters.value,
            page: page
        },
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
}

const handleFilterChange = useDebounceFn(() => {
    router.visit(props.submissions.path, {
        data: filters.value,
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
}, 300)

const updateFilter = (column: string, value: string) => {
    filters.value[column] = value
    handleFilterChange()
}
</script>

<template>

    <Head :title="`Submissions for ${props.link.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${props.activityId}/links/${props.link.id}`" type="token"
                buttonText="Delete Token" :item-name="props.link.name" />
        </template>

        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <h2 class="scroll-m-20 text-3xl font-semibold tracking-tight transition-colors first:mt-0">
                    Submissions for {{ props.link.name }}
                </h2>
                <p class="text-sm text-muted-foreground">{{ props.activityTitle }}</p>
            </div>

            <DataTable :columns="columns" :data="props.submissions.data" :pagination="props.submissions" :filter-config="[
                { column: 'student_name', placeholder: 'Filter by Student Name' },
                { column: 'student_no', placeholder: 'Search Student No.' }
            ]" :filter-values="filters" @page-change="handlePageChange" @filter-change="updateFilter" :show-detect-button="true" />
        </div>
    </AppLayout>
</template>
