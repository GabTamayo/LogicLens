<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Deferred } from '@inertiajs/vue3'
import { Head, router, useRemember } from '@inertiajs/vue3'
import { type BreadcrumbItem, Submission } from '@/types'
import AlertDialogDelete from '@/components/AlertDialogDelete.vue'
import DataTable from '@/components/DataTable.vue'
import { columns } from '@/components/submissions/columns'
import { useDebounceFn } from '@vueuse/core'
import { LoaderCircle } from 'lucide-vue-next'
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner'
import 'vue-sonner/style.css';
import { ref } from 'vue'

const props = defineProps<Submission & { filters: Record<string, string> }>()
const isDetecting = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.name, href: `/activities/${props.activityId}/links/${props.link.id}` }
]

const filters = useRemember({
    student_name: props.filters?.student_name || '',
    student_no: props.filters?.student_no || '',
}, 'submissions-filters')

const handleDetectSubmission = () => {
    if (isDetecting.value) return

    isDetecting.value = true

    router.post(`/activities/${props.activityId}/links/${props.link.id}/detect`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Detection successfull!');
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors)[0] as string
            toast.error(errorMessage || 'Detection failed')
            isDetecting.value = false
        },
    })
}

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

            <Deferred data="submissions">
                <template #fallback>
                    <div class="flex items-center justify-center gap-2 h-64 border rounded-md">
                        <span class="text-muted-foreground">Loading submissions</span>
                        <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                    </div>
                </template>
                <DataTable :columns="columns" :data="props.submissions.data" :pagination="props.submissions"
                    :filter-config="[
                        { column: 'student_name', placeholder: 'Filter by Student Name' },
                        { column: 'student_no', placeholder: 'Search Student No.' }
                    ]" :filter-values="filters" @page-change="handlePageChange" @filter-change="updateFilter"
                    :is-detecting="isDetecting" @detect-submission="handleDetectSubmission"
                    :show-detect-button="true" />
            </Deferred>
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
