<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem, Submission } from '@/types'
import { Button } from "@/components/ui/button"
import { Search } from 'lucide-vue-next';
import AlertDialogDelete from '@/components/AlertDialogDelete.vue'
import DataTable from '@/components/DataTable.vue';
import { columns } from '@/components/submissions/columns';

const props = defineProps<Submission>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.activityTitle, href: `/activities/${props.activityId}` },
    { title: props.link.name, href: `/activities/${props.activityId}/links/${props.link.id}` }
];

const handlePageChange = (page: number) => {
    router.visit(`${props.submissions.path}?page=${page}`, { preserveScroll: true })
}
</script>

<template>

    <Head :title="`Submissions for ${props.link.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${props.activityId}/links/${props.link.id}`" type="token"
                buttonText="Delete Token" />
        </template>

        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-between items-start space-x-4">
                <div>
                    <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Submissions for {{ props.link.name }}
                    </h4>
                    <p class="text-sm text-muted-foreground">{{ props.activityTitle }}</p>
                </div>
                <div>
                    <Button class="cursor-pointer">
                        <Search />Detect Submission
                    </Button>
                </div>
            </div>
            <DataTable :columns="columns" :data="props.submissions.data" :pagination="props.submissions" :filter-config="[
                { column: 'student_name', placeholder: 'Filter by student name...' },
                { column: 'student_no', placeholder: 'Search by Student No.' }
            ]" @page-change="handlePageChange" />
        </div>
    </AppLayout>
</template>
