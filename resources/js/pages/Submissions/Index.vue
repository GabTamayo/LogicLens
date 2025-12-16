<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Deferred, Link } from '@inertiajs/vue3'
import { Head, router, useRemember } from '@inertiajs/vue3'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Separator } from '@/components/ui/separator'
import { type BreadcrumbItem, type ActivityLink, Submission, DetectionPageProps } from '@/types'
import AlertDialogDelete from '@/components/AlertDialogDelete.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import DataTable from '@/components/DataTable.vue'
import { columns as submissionColumns } from '@/components/submissions/columns'
import { columns as detectionColumns } from '@/components/detections/columns'
import { useDebounceFn } from '@vueuse/core'
import { LoaderCircle, Circle, CalendarCheck, FileText, Shield, ArrowUpDown } from 'lucide-vue-next'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'
import 'vue-sonner/style.css'
import { ref, watch, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useDeadline } from '@/composables/useDeadline'
import Button from '@/components/ui/button/Button.vue'

interface TabbedPageProps {
    activityId: number
    activityTitle: string
    link: ActivityLink & { created_at: string }
    filters: Record<string, string>
    submissions?: Submission['submissions'] & { data: any[], path: string }
    detections?: DetectionPageProps['detections']
    activeTab: string
}

const props = defineProps<TabbedPageProps>()

// Deadline utilities
const { formatRelativeDeadline, formatExpiresAt, getDeadlineStatus } = useDeadline()

// State management
const isDetecting = ref(false)
const activeTab = ref(props.activeTab || 'submission')
const submissionPage = ref(1)
const detectionPage = ref(1)
const isInitialLoadDone = ref(false)

// Computed properties
const hasDeadline = computed(() => !!props.link.expires_at)

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.course?.name || 'Course', href: `/activities/${props.activityId}/links/${props.link.id}` }
]

// Filters
const submissionFilters = useRemember({
    student_name: props.filters?.student_name || '',
    student_no: props.filters?.student_no || '',
}, 'submissions-filters')

const detectionFilters = useRemember({
    student_name_a: props.filters?.student_name_a || '',
    student_name_b: props.filters?.student_name_b || '',
}, 'detection-filters')

// Sort
const submissionSort = ref(props.filters?.sort || 'newest')
const detectionSort = ref(props.filters?.sort || 'score_desc')

// Tab change handler
watch(activeTab, (newTab) => {
    const page = newTab === 'detection' ? detectionPage.value : submissionPage.value
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { tab: newTab, page },
        preserveScroll: true,
        preserveState: true,
        only: newTab === 'detection' ? ['detections', 'activeTab'] : ['submissions', 'activeTab'],
    })
})

// Detection handler
const handleDetectSubmission = () => {
    if (isDetecting.value) return

    isDetecting.value = true

    router.post(`/activities/${props.activityId}/links/${props.link.id}/detect`, {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.successMessage) {
                toast.success(page.props.successMessage)
            } else {
                toast.success('Detection complete', {
                    description: 'Plagiarism detection has been completed successfully.',
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

// Sort handlers
watch(submissionSort, () => {
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...submissionFilters.value, sort: submissionSort.value, tab: 'submission' },
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
})

watch(detectionSort, () => {
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...detectionFilters.value, sort: detectionSort.value, tab: 'detection' },
        preserveScroll: true,
        preserveState: true,
        only: ['detections'],
    })
})

// Pagination handlers
const handleSubmissionPageChange = (page: number) => {
    submissionPage.value = page
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...submissionFilters.value, sort: submissionSort.value, page, tab: 'submission' },
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
}

const handleDetectionPageChange = (page: number) => {
    detectionPage.value = page
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...detectionFilters.value, sort: detectionSort.value, page, tab: 'detection' },
        preserveScroll: true,
        preserveState: true,
        only: ['detections'],
    })
}

// Filter change handlers
const handleSubmissionFilterChange = useDebounceFn(() => {
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...submissionFilters.value, sort: submissionSort.value, tab: 'submission' },
        preserveScroll: true,
        preserveState: true,
        only: ['submissions'],
    })
}, 300)

const handleDetectionFilterChange = useDebounceFn(() => {
    router.visit(`/activities/${props.activityId}/links/${props.link.id}`, {
        data: { ...detectionFilters.value, sort: detectionSort.value, tab: 'detection' },
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
</script>

<template>

    <Head :title="`Submissions for ${props.link.course?.name || 'Course'}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${props.activityId}/links/${props.link.id}`" type="link"
                buttonText="Delete Link" :item-name="props.link.course?.name || 'this link'" />
        </template>

        <div class="flex h-full flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Header Card -->
            <Card>
                <CardHeader>
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-wrap items-center gap-3 mb-1.5">
                                <Button variant="link" class="p-0 h-auto text-start" as-child>
                                    <Link :href="`/courses/${props.link.course?.id}`" prefetch="mount">
                                        <CardTitle class="text-2xl">{{ props.link.course?.name || 'Course' }}</CardTitle>
                                    </Link>
                                </Button>
                                <Badge variant="outline" class="h-6">
                                    <Circle class="mr-1.5 size-4" :class="link.is_open
                                        ? 'fill-green-500 text-green-500'
                                        : 'fill-red-500 text-red-500'" />
                                    {{ link.is_open ? 'Open' : 'Closed' }}
                                </Badge>
                            </div>
                            <CardDescription class="flex flex-wrap items-center gap-2 pb-1.5">
                                <span>{{ props.activityTitle }}</span>
                                <Separator orientation="vertical" class="h-4" />
                                <span class="flex items-center gap-1.5">
                                    <CalendarCheck class="h-3.5 w-3.5" />
                                    {{ new Date(props.link.created_at).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    }) }}
                                </span>
                                <template v-if="hasDeadline">
                                    <Separator orientation="vertical" class="h-4" />
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <span class="flex items-center gap-1.5 cursor-default"
                                                    :class="getDeadlineStatus(link.expires_at)?.class">
                                                    <component :is="getDeadlineStatus(link.expires_at)?.icon"
                                                        class="h-3.5 w-3.5" />
                                                    {{ formatRelativeDeadline(link.expires_at) }}
                                                </span>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p class="font-medium">{{ formatExpiresAt(link.expires_at) }}</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </template>

                                <template v-else>
                                    <Separator orientation="vertical" class="h-4" />
                                    <span class="text-muted-foreground">
                                        No Deadline
                                    </span>
                                </template>
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Tabs Section -->
            <Card>
                <CardContent class="p-0">
                    <Tabs v-model="activeTab" class="w-full">
                        <div class="border-b px-6">
                            <TabsList class="h-auto rounded-none border-b-0 bg-transparent p-0">
                                <TabsTrigger value="submission"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pb-3 pt-2 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none">
                                    Submissions
                                </TabsTrigger>
                                <TabsTrigger value="detection"
                                    class="relative rounded-none border-b-2 border-transparent px-4 pb-3 pt-2 font-semibold shadow-none transition-none data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-primary data-[state=active]:shadow-none">
                                    Detections
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <TabsContent value="submission" class="m-0 p-6">
                            <template v-if="props.submissions">
                                <Deferred data="submissions" @resolve="isInitialLoadDone = true">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading submissions...</span>
                                        </div>
                                    </template>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-2">
                                            <Select v-model="submissionSort" aria-label="Sort submissions">
                                                <SelectTrigger class="w-[200px]">
                                                    <ArrowUpDown class="h-4 w-4 mr-2" />
                                                    <SelectValue placeholder="Sort by" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectLabel>Sort By</SelectLabel>
                                                        <SelectItem value="newest">Earliest Submission</SelectItem>
                                                        <SelectItem value="oldest">Latest Submission</SelectItem>
                                                        <SelectItem value="name_asc">Name (A-Z)</SelectItem>
                                                        <SelectItem value="name_desc">Name (Z-A)</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <DataTable :columns="submissionColumns" :data="props.submissions.data"
                                            :pagination="props.submissions as any" :filter-config="[
                                                { column: 'student_name', placeholder: 'Filter by Student Name' },
                                                { column: 'student_no', placeholder: 'Search Student No.' }
                                            ]" :filter-values="submissionFilters"
                                            @page-change="handleSubmissionPageChange"
                                            @filter-change="updateSubmissionFilter" :is-detecting="isDetecting"
                                            @detect-submission="handleDetectSubmission" :show-detect-button="true" />
                                    </div>
                                </Deferred>
                            </template>

                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading submissions...</span>
                            </div>
                        </TabsContent>

                        <TabsContent value="detection" class="m-0 p-6">
                            <template v-if="props.detections">
                                <Deferred data="detections">
                                    <template #fallback>
                                        <div class="flex items-center justify-center gap-2 rounded-md p-12">
                                            <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                            <span class="text-muted-foreground">Loading detection results...</span>
                                        </div>
                                    </template>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-2">
                                            <Select v-model="detectionSort" aria-label="Sort detections">
                                                <SelectTrigger class="w-[200px]">
                                                    <ArrowUpDown class="h-4 w-4 mr-2" />
                                                    <SelectValue placeholder="Sort by Score" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectLabel>Sort By Score</SelectLabel>
                                                        <SelectItem value="score_desc">Descending</SelectItem>
                                                        <SelectItem value="score_asc">Ascending</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <DataTable :columns="detectionColumns"
                                            :data="(props.detections?.data as any) || []"
                                            :pagination="props.detections as any" :filter-config="[
                                                { column: 'student_name_a', placeholder: 'Filter by Student A Name' },
                                                { column: 'student_name_b', placeholder: 'Filter by Student B Name' },
                                            ]" :filter-values="detectionFilters"
                                            @page-change="handleDetectionPageChange"
                                            @filter-change="updateDetectionFilter" :show-detect-button="false" />
                                    </div>
                                </Deferred>
                            </template>
                            <div v-else class="flex items-center justify-center gap-2 rounded-md p-12">
                                <LoaderCircle class="h-6 w-6 animate-spin text-muted-foreground" />
                                <span class="text-muted-foreground">Loading detection results...</span>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
