<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, SortingState, VisibilityState, ExpandedState } from '@tanstack/vue-table'
import { h, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Settings2 } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getSortedRowModel, getExpandedRowModel, useVueTable } from '@tanstack/vue-table'
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger, } from '@/components/ui/dropdown-menu'
import { valueUpdater } from './ui/table/utils'
import { Input } from '@/components/ui/input'
import { computed } from 'vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination'

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
    filterValues?: Record<string, string>
}>()
const sorting = ref<SortingState>([])
const columnVisibility = ref<VisibilityState>({})
const expanded = ref<ExpandedState>({})

const emit = defineEmits<{
    'page-change': [page: number]
    'filter-change': [column: string, value: string]
}>()
const reactiveData = computed(() => props.data ?? [])
const reactiveColumns = computed(() => props.columns ?? [])

const filterConfigs = computed(() => {
    if (!props.filterConfig) return []
    return Array.isArray(props.filterConfig) ? props.filterConfig : [props.filterConfig]
})

const table = computed(() => useVueTable({
    data: reactiveData.value,
    columns: reactiveColumns.value,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
    state: {
        get sorting() { return sorting.value },
        get columnVisibility() { return columnVisibility.value },
        get expanded() { return expanded.value },
    },
}))

const handlePageChange = (page: number) => {
    emit('page-change', page)
}
const handleFilterInput = (column: string, value: string) => {
    emit('filter-change', column, value)
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="filterConfigs.length > 0" class="flex items-center gap-4 py-2">
            <Input v-for="(filter, index) in filterConfigs" :key="index" class="max-w-sm"
                :placeholder="filter.placeholder || `Filter ${filter.column}...`"
                :model-value="filterValues?.[filter.column] || ''"
                @update:model-value="handleFilterInput(filter.column, $event)" />
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="ml-auto text-sm cursor-pointer">
                        <Settings2 class="w-4 h-4 mr-2" />
                        View
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuCheckboxItem
                        v-for="column in table.getAllColumns().filter((column) => column.getCanHide())" :key="column.id"
                        class="" :modelValue="column.getIsVisible()" @update:modelValue="(value) => {
                            column.toggleVisibility(!!value)
                        }">
                        {{ column.columnDef.label || column.id }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>
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
                        <template v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableRow :data-state="row.getIsSelected() ? 'selected' : undefined">
                                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="row.getIsExpanded()">
                                <TableCell :colspan="row.getAllCells().length">
                                    <code
                                        class="relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm font-semibold">
                                    {{ JSON.stringify(row.original) }}
                                    </code>
                                </TableCell>
                            </TableRow>
                        </template>
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
