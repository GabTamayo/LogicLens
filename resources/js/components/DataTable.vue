<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, VisibilityState, ExpandedState } from '@tanstack/vue-table'
import { h, ref, onMounted, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { ButtonGroup, ButtonGroupSeparator } from '@/components/ui/button-group'
import { EllipsisVertical, FileScan } from 'lucide-vue-next'
import { FlexRender, getCoreRowModel, getExpandedRowModel, useVueTable } from '@tanstack/vue-table'
import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger, } from '@/components/ui/dropdown-menu'
import { valueUpdater } from './ui/table/utils'
import { Input } from '@/components/ui/input'
import { computed } from 'vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import type { PaginationData } from '@/types'
import PaginationComponent from '@/components/Pagination.vue'
import debounce from 'lodash/debounce'
// Import Prism.js
import Prism from 'prismjs'
import 'prismjs/themes/prism-tomorrow.css'
// Import language support
import 'prismjs/components/prism-java'
// Import line numbers plugin
import 'prismjs/plugins/line-numbers/prism-line-numbers.css'
import 'prismjs/plugins/line-numbers/prism-line-numbers'

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
    showDetectButton?: boolean
}>()
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
    getExpandedRowModel: getExpandedRowModel(),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
    state: {
        get columnVisibility() { return columnVisibility.value },
        get expanded() { return expanded.value },
    },
}))

const handlePageChange = (page: number) => {
    emit('page-change', page)
}
const handleFilterInput = debounce((column: string, value: string) => {
    const formattedValue =
        column === 'student_name'
            ? value.replace(/\b\w/g, c => c.toUpperCase())
            : value
    emit('filter-change', column, formattedValue)
}, 1000)
const getLanguageFromExtension = (extension?: string): string => {
    if (!extension) return 'plaintext'

    const languageMap: Record<string, string> = {
        txt: 'java'
    }
    return languageMap[extension.toLowerCase()] || 'plaintext'
}

watch(expanded, () => {
    setTimeout(() => { Prism.highlightAll() }, 50)
}, { deep: true })

onMounted(() => {
    Prism.highlightAll()
})
</script>

<template>
    <div class="space-y-4">
        <div v-if="filterConfigs.length > 0" class="flex items-center gap-4 py-2">
            <Input v-for="(filter, index) in filterConfigs" :key="index" class="max-w-sm"
                :placeholder="filter.placeholder || `Filter ${filter.column}...`"
                :model-value="filterValues?.[filter.column] || ''"
                @update:model-value="handleFilterInput(filter.column, $event)" />
            <ButtonGroup>
                <Button variant="secondary" class="w-full">
                    <FileScan />Detect Submission
                </Button>
                <ButtonGroupSeparator />
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="secondary" size="icon">
                            <EllipsisVertical />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-61">
                        <DropdownMenuCheckboxItem
                            v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                            :key="column.id" class="" :modelValue="column.getIsVisible()" @update:modelValue="(value) => {
                                column.toggleVisibility(!!value)
                            }">
                            {{ column.columnDef.label || column.id }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </ButtonGroup>
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
                                    <div class="max-h-120 overflow-auto">
                                        <pre class="line-numbers !m-0 !rounded-none"><code :class="`language-${getLanguageFromExtension(row.original.file_extension)}`">{{ row.original.file_content }}
                                            </code>
                                        </pre>
                                    </div>
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
        <PaginationComponent v-if="pagination" :pagination="pagination" @page-change="handlePageChange" />
    </div>
</template>
