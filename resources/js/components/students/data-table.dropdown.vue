<script setup lang="ts">
import { MoreHorizontal, BookUser } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger, } from '@/components/ui/dropdown-menu'
import type { StudentRow } from '@/components/students/columns'

const { student } = defineProps<{ student: StudentRow }>()
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0 cursor-pointer">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem disabled>
                <BookUser class="w-4 h-4 mr-2" />
                View Details
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
