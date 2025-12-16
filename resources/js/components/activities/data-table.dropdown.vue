<script setup lang="ts">
import { MoreHorizontal, Eye, TextInitial, CircleMinus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/vue3'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger, DropdownMenuSeparator } from '@/components/ui/dropdown-menu'
import type { ActivityRow } from '@/components/activities/columns'

const { activity } = defineProps<{ activity: ActivityRow }>()
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
            <DropdownMenuItem as-child>
                <Link :href="`/activities/${activity.activity_id}`" prefetch="mount">
                    <TextInitial class="w-4 h-4 mr-2" />
                    View Content
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link :href="`/activities/${activity.activity_id}/links/${activity.id}`" prefetch="mount">
                    <Eye class="w-4 h-4 mr-2" />
                    View Submissions
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
