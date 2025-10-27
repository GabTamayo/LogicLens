<script setup lang="ts">
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination'
import type { PaginationData } from '@/types'

defineProps<{ pagination: PaginationData }>()

const emit = defineEmits<{
    'page-change': [page: number]
}>()

const handlePageChange = (page: number) => {
    emit('page-change', page)
}
</script>

<template>
    <Pagination v-if="pagination && pagination.last_page > 1" v-slot="{ page }"
        class="flex items-center justify-end gap-4" :total="pagination.total" :items-per-page="pagination.per_page"
        :default-page="pagination.current_page" :sibling-count="1" show-edges @update:page="handlePageChange">
        <div class="text-sm text-muted-foreground">
            Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }} results
        </div>

        <PaginationContent v-slot="{ items }">
            <PaginationPrevious />
            <template v-for="(item, index) in items" :key="index">
                <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page">
                    {{ item.value }}
                </PaginationItem>
                <PaginationEllipsis v-else :index="index" />
            </template>
            <PaginationNext />
        </PaginationContent>
    </Pagination>
</template>
