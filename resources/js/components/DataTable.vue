<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, ColumnFiltersState, SortingState } from '@tanstack/vue-table'
import { FlexRender, getCoreRowModel, getFilteredRowModel, getSortedRowModel, useVueTable, } from '@tanstack/vue-table'
import { valueUpdater } from './ui/table/utils'
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { h, ref } from 'vue'
import { computed } from 'vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious, } from '@/components/ui/pagination'

export interface PaginationData {
    current_page: number
    per_page: number
    total: number
    from: number
    to: number
    last_page: number
}

export interface FilterConfig {
    column: string
    placeholder?: string
}

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    pagination?: PaginationData
    filterConfig?: FilterConfig | FilterConfig[]
}>()
const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const emit = defineEmits<{
    'page-change': [page: number]
}>()
const reactiveData = computed(() => props.data ?? [])
const reactiveColumns = computed(() => props.columns ?? [])
const filterConfigs = computed(() => {
    if (!props.filterConfig) return []
    return Array.isArray(props.filterConfig) ? props.filterConfig : [props.filterConfig]
})
const table = useVueTable({
    data: reactiveData.value,
    columns: reactiveColumns.value,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
    },
})
const handlePageChange = (page: number) => {
    emit('page-change', page)
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="filterConfigs.length > 0" class="flex items-center gap-4 py-4">
            <Input
                v-for="(filter, index) in filterConfigs"
                :key="index"
                class="max-w-sm"
                :placeholder="filter.placeholder || `Filter ${filter.column}...`"
                :model-value="table.getColumn(filter.column)?.getFilterValue() as string"
                @update:model-value="table.getColumn(filter.column)?.setFilterValue($event)"
            />
        </div>
        <div class="border rounded-md">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center text-muted-foreground">
                                No results.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

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
    </div>
</template>
