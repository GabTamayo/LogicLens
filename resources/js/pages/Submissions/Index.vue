<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Deferred } from '@inertiajs/vue3'
import { Head, router, useRemember } from '@inertiajs/vue3'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Separator } from '@/components/ui/separator'
import { type BreadcrumbItem, Submission, DetectionPageProps } from '@/types'
import AlertDialogDelete from '@/components/AlertDialogDelete.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import DataTable from '@/components/DataTable.vue'
import { columns as submissionColumns } from '@/components/submissions/columns'
import { columns as detectionColumns } from '@/components/detections/columns'
import { useDebounceFn } from '@vueuse/core'
import { LoaderCircle, Circle } from 'lucide-vue-next'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'
import 'vue-sonner/style.css'
import { ref, watch } from 'vue'

interface TabbedPageProps {
    activityId: number
    activityTitle: string
    link: {
        id: number
        name: string
        token: string
        is_open: boolean
        expires_at: string | null
        created_at: string
    }
    filters: Record<string, string>
    submissions?: Submission['submissions'] & { data: any[], path: string }
    detections?: DetectionPageProps['detections']
    activeTab: string
}

const props = defineProps<TabbedPageProps>()
const isDetecting = ref(false)
const activeTab = ref(props.activeTab || 'submission')
const submissionPage = ref(1)
const detectionPage = ref(1)
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.name, href: `/activities/${props.activityId}/links/${props.link.id}` }
]
const submissionFilters = useRemember({
    student_name: props.filters?.student_name || '',
    student_no: props.filters?.student_no || '',
}, 'submissions-filters')
const detectionFilters = useRemember({
    student_name_a: props.filters?.student_name_a || '',
    student_name_b: props.filters?.student_name_b || '',
}, 'detection-filters')
watch(activeTab, (newTab) => {
    const page = newTab === 'detection' ? detectionPage.value : submissionPage.value
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { tab: newTab, page },
        preserveScroll: true,
        preserveState: true,
        only: newTab === 'detection' ? ['detections', 'activeTab'] : ['submissions', 'activeTab'],
    })
})
const handleDetectSubmission = () => {
    if (isDetecting.value) return

    isDetecting.value = true

    router.post(`/activities/${props.activityId}/links/${props.link.id}/detect`, {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.successMessage) {
                toast.success(page.props.successMessage)
            } else {
                toast.success('Detection successful!', {
                    description: 'You can view the result'
                })
            }
            isDetecting.value = false
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors)[0] as string || 'Detection failed'
            toast.error('Unable to run detection', {
                description: errorMessage
            })
            isDetecting.value = false
        },
    })
}
const handleSubmissionPageChange = (page: number) => {
    submissionPage.value = page
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...submissionFilters.value, page, tab: 'submission' },
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
}
const handleDetectionPageChange = (page: number) => {
    detectionPage.value = page
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...detectionFilters.value, page, tab: 'detection' },
        preserveScroll: true,
        preserveState: true,
        only: ['detections'],
    })
}
const handleSubmissionFilterChange = useDebounceFn(() => {
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...submissionFilters.value, tab: 'submission' },
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
}, 300)
const handleDetectionFilterChange = useDebounceFn(() => {
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...detectionFilters.value, tab: 'detection' },
        preserveScroll: true,
        preserveState: true,
        only: ['detections'],
    })
}, 300)
const updateSubmissionFilter = (column: string, value: string) => {
    submissionFilters.value[column] = value
    handleSubmissionFilterChange()
}
const updateDetectionFilter = (column: string, value: string) => {
    detectionFilters.value[column] = value
    handleDetectionFilterChange()
}
const isInitialLoadDone = ref(false)
</script>

<template>

    <Head :title="`Submissions for ${props.link.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${props.activityId}/links/${props.link.id}`" type="link"
                buttonText="Delete Link" :item-name="props.link.name" />
        </template>

        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <div class="inline-flex items-center gap-2">
                    <h2 class="scroll-m-20 text-3xl font-semibold tracking-tight">
                        {{ props.link.name }}
                    </h2>
                    <Badge variant="outline" class="h-6">
                        <Circle class="size-4" :class="link.is_open
                            ? 'fill-green-500 text-green-500'
                            : 'fill-red-500 text-red-500'" />
                        {{ link.is_open ? 'Open' : 'Closed' }}
                    </Badge>
                </div>
                <div class="flex items-center gap-2">
                    <p class="text-sm text-muted-foreground">{{ props.activityTitle }}</p>

                    <Separator orientation="vertical" class="h-4" />

                    <p class="text-xs text-muted-foreground">
                        {{ new Date(props.link.created_at).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }) }}
                    </p>
                </div>


                <Tabs v-model="activeTab" class="mt-2">
                    <TabsList>
                        <TabsTrigger value="submission">
                            Submissions
                        </TabsTrigger>
                        <TabsTrigger value="detection">
                            Detections
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="submission">
                        <template v-if="props.submissions">
                            <Deferred data="submissions" @resolve="isInitialLoadDone = true">
                                <template #fallback>
                                    <div class="flex items-center justify-center gap-2 h-64 border rounded-md">
                                        <span class="text-muted-foreground">Loading submissions</span>
                                        <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                    </div>
                                </template>
                                <DataTable :columns="submissionColumns" :data="props.submissions.data"
                                    :pagination="props.submissions" :filter-config="[
                                        { column: 'student_name', placeholder: 'Filter by Student Name' },
                                        { column: 'student_no', placeholder: 'Search Student No.' }
                                    ]" :filter-values="submissionFilters" @page-change="handleSubmissionPageChange"
                                    @filter-change="updateSubmissionFilter" :is-detecting="isDetecting"
                                    @detect-submission="handleDetectSubmission" :show-detect-button="true" />
                            </Deferred>
                        </template>
                        <div v-else class="flex items-center justify-center gap-2 h-64 border rounded-md">
                            <span class="text-muted-foreground">Loading submissions</span>
                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                        </div>
                    </TabsContent>

                    <TabsContent value="detection">
                        <template v-if="props.detections">
                            <Deferred data="detections">
                                <template #fallback>
                                    <div class="flex items-center justify-center gap-2 h-64 border rounded-md">
                                        <span class="text-muted-foreground">Loading detection results</span>
                                        <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                    </div>
                                </template>
                                <DataTable :columns="detectionColumns" :data="props.detections?.data || []"
                                    :pagination="props.detections" :filter-config="[
                                        { column: 'student_name_a', placeholder: 'Filter by Student A Name' },
                                        { column: 'student_name_b', placeholder: 'Filter by Student B Name' },
                                    ]" :filter-values="detectionFilters" @page-change="handleDetectionPageChange"
                                    @filter-change="updateDetectionFilter" :show-detect-button="false" />
                            </Deferred>
                        </template>
                        <div v-else class="flex items-center justify-center gap-2 h-64 border rounded-md">
                            <span class="text-muted-foreground">Loading detection results</span>
                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
