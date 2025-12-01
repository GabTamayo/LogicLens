<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { Separator } from '@/components/ui/separator';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { Item, ItemContent, ItemDescription, ItemFooter, ItemHeader, ItemMedia, ItemTitle, ItemGroup, ItemSeparator, ItemActions } from '@/components/ui/item'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import { Tabs, TabsContent, TabsList, TabsTrigger, } from '@/components/ui/tabs'
import { Circle } from 'lucide-vue-next';
import SelectSeparator from '@/components/ui/select/SelectSeparator.vue';

const activeLinksData = {
    total: 14,
    noDeadline: 8,
    addedThisWeek: 6,
    dueThisWeek: [
        { id: 1, course: 'BSIT-1', activity: 'Activity 1', deadline: '2024-11-28' },
        { id: 2, course: 'BSIT-2', activity: 'Midterm Exam', deadline: '2024-11-29' },
        { id: 3, course: 'BSIT-3', activity: 'Project Proposal', deadline: '2024-11-30' },
        { id: 4, course: 'BSIT-4', activity: 'Lab Exercise 4', deadline: '2024-12-01' },
        { id: 5, course: 'BSIT-1', activity: 'Quiz 3', deadline: '2024-12-02' },
        { id: 6, course: 'BSIT-2', activity: 'Essay 2', deadline: '2024-12-03' },
    ]
};
</script>

<template>
    <Modal position="top" panel-classes="bg-white rounded-lg p-6 dark:bg-[hsl(222.2_84%_4.9%)] w-full max-w-xl">
        <div class="tracking-tight space-y-6">

            <!-- Header -->
            <div class="inline-flex items-center space-x-2">
                <Circle class="size-3 text-green-600" :stroke="'none'" :fill="'currentColor'" />
                <h1 class="text-sm sm:text-md font-bold text-muted-foreground">Active Links</h1>
            </div>

            <div class="flex justify-between">
                <!-- Total -->
                <div class="text-4xl sm:text-6xl font-semibold tabular-nums">
                    {{ activeLinksData.total }}
                </div>

                <!-- Stats Section -->
                <div class="flex flex-col items-end text-center">
                    <div class="flex items-center gap-2">
                        <div class="flex flex-col items-center">
                            <div class="text-2xs sm:text-xs text-muted-foreground">No Deadline</div>
                            <div class="text-xl sm:text-3xl font-semibold">
                                {{ activeLinksData.noDeadline }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Separator />

            <div>
                <ItemGroup v-if="activeLinksData.dueThisWeek.length > 0">
                    <ScrollArea class="h-58 w-full rounded-md">
                        <div class="p-1">
                            <template v-for="(item, index) in activeLinksData.dueThisWeek" :key="item.id">
                                <Item role="listitem" as-child>
                                    <a href="#">
                                        <ItemContent>
                                            <ItemTitle class="text-sm font-medium">
                                                {{ item.course }}
                                            </ItemTitle>
                                            <ItemDescription class="text-xs">
                                                {{ item.activity }}
                                            </ItemDescription>
                                        </ItemContent>
                                        <ItemContent>
                                            <ItemDescription>11/21/2025</ItemDescription>
                                        </ItemContent>
                                    </a>
                                </Item>
                                <ItemSeparator v-if="index < activeLinksData.dueThisWeek.length - 1" />
                            </template>
                        </div>
                    </ScrollArea>
                </ItemGroup>

                <div v-else class="text-center py-8 text-muted-foreground text-sm">
                    No items due this week
                </div>
            </div>

        </div>
    </Modal>
</template>
