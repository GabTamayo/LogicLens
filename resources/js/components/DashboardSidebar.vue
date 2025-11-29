<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { TrendingDown, TrendingUp, Circle, FileScan, Flag, SquareArrowOutUpRight, Eye, FlagOff, Calendar } from "lucide-vue-next"
import { Separator } from '@/components/ui/separator';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { Item, ItemContent, ItemDescription, ItemFooter, ItemHeader, ItemMedia, ItemTitle, ItemGroup, ItemSeparator, ItemActions } from '@/components/ui/item'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, SelectSeparator } from '@/components/ui/select'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, } from '@/components/ui/tooltip'
import Button from "./ui/button/Button.vue"
import Badge from './ui/badge/Badge.vue';

const showSidebar = ref(false);
onMounted(() => {
    showSidebar.value = true;
});
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

const flaggedDetections = [
    { id: 1, course: 'BSIT-1', activity: 'Kazuya Mishima & Capstone Master', percent: '100%' },
    { id: 2, course: 'BSIT-2', activity: 'Kazuya Mishima & Capstone Master', percent: '76%' },
    { id: 3, course: 'BSIT-3', activity: 'Kazuya Mishima & Capstone Master', percent: '100%' },
    { id: 4, course: 'BSIT-1', activity: 'Kazuya Mishima & Capstone Master', percent: '76%' },
    { id: 5, course: 'BSIT-4', activity: 'Kazuya Mishima & Capstone Master', percent: '89%' },
    { id: 6, course: 'BSIT-2', activity: 'Kazuya Mishima & Capstone Master', percent: '92%' },
]
</script>

<template>
    <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-75"
        enter-to-class="opacity-100 scale-100">
        <div v-if="showSidebar"
            class="w-40 h-40 rounded-full border-6 flex flex-col items-center justify-center border-green-500 mx-auto">
            <span class="text-xl sm:text-5xl font-black leading-none">40%</span>
            <span class="text-base mt-1 text-muted-foreground">Overall</span>
        </div>
    </transition>

    <!-- Left-aligned text -->
    <div class="text-left w-full">
        <div class="flex justify-between items-center mb-2">
            <div class="inline-flex gap-2 items-center text-muted-foreground">
                <p class="font-medium">Upcoming Deadline</p>
                <Calendar class="size-3" />
            </div>
            <Select>
                <SelectTrigger class="w-[130px]">
                    <SelectValue placeholder="Filter Due" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Due Date</SelectLabel>
                        <SelectItem value="all">
                            All
                        </SelectItem>
                        <SelectItem value="this_day">
                            This day
                        </SelectItem>
                        <SelectItem value="this_week">
                            This week
                        </SelectItem>
                        <SelectItem value="this_month">
                            This month
                        </SelectItem>
                        <SelectSeparator />
                        <SelectItem value="no_deadline">
                            No deadline
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
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
                                        <ItemDescription>In about 4 days</ItemDescription>
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

    <div class="text-left w-full">
        <div class="inline-flex items-center mb-2 gap-2 text-muted-foreground">
            <p class="font-medium">Flagged Detections</p>
            <Flag class="size-3" />
        </div>
        <ItemGroup v-if="flaggedDetections.length > 0">
            <ScrollArea class="h-[280px] w-full">
                <div class="p-1">
                    <template v-for="(item, index) in flaggedDetections" :key="item.id">
                        <Item>
                            <ItemContent>
                                <ItemTitle class="flex justify-between items-center">
                                    <div>
                                        {{ item.course }}
                                        <span class="text-muted-foreground">(Activity 1)</span>
                                    </div>
                                    <Badge>{{ item.percent }}</Badge>
                                </ItemTitle>
                                <ItemDescription>
                                    {{ item.activity }}
                                </ItemDescription>
                            </ItemContent>
                            <ItemActions>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button size="icon-sm" variant="outline" class="rounded-full">
                                                <FlagOff class="text-destructive" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            Remove Flag
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button size="icon-sm" variant="outline" class="rounded-full">
                                                <Eye />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            View Comparison
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button size="icon-sm" variant="outline" class="rounded-full">
                                                <SquareArrowOutUpRight />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            View Detection List
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </ItemActions>
                        </Item>
                        <ItemSeparator v-if="index < flaggedDetections.length - 1" />
                    </template>
                </div>
            </ScrollArea>
        </ItemGroup>
    </div>
</template>
