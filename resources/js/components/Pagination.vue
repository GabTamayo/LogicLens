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
        class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4" :total="pagination.total"
        :items-per-page="pagination.per_page" :default-page="pagination.current_page" :sibling-count="1" show-edges
        @update:page="handlePageChange">

        <!-- Results text - hidden on mobile, shown on tablet+ -->
        <div class="hidden sm:block text-xs sm:text-sm text-muted-foreground order-1">
            Showing {{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }} results
        </div>

        <!-- Pagination controls -->
        <PaginationContent v-slot="{ items }" class="order-2 sm:order-2">
            <PaginationPrevious class="h-9 w-9 sm:h-10 sm:w-auto" />

            <template v-for="(item, index) in items" :key="index">
                <!-- Show fewer page numbers on mobile -->
                <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === page"
                    class="h-9 w-9 sm:h-10 sm:w-10">
                    {{ item.value }}
                </PaginationItem>
                <PaginationEllipsis v-else :index="index" class="hidden sm:flex" />
            </template>

            <PaginationNext class="h-9 w-9 sm:h-10 sm:w-auto" />
        </PaginationContent>

        <!-- Mobile results text - compact version -->
        <div class="sm:hidden text-xs text-muted-foreground order-3">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </div>
    </Pagination>
</template>
