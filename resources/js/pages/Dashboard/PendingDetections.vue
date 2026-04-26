<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Item, ItemContent, ItemDescription, ItemGroup, ItemSeparator, ItemTitle } from '@/components/ui/item';
import { ScrollArea } from '@/components/ui/scroll-area';
import Separator from '@/components/ui/separator/Separator.vue';
import { type PendingDetection } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Deferred, Modal } from '@inertiaui/modal-vue';
import { differenceInDays, format, isThisMonth, isThisWeek, isToday, isYesterday, parseISO } from 'date-fns';
import { AlertTriangle, Calendar, CircleCheck, Loader } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    pendingDetections: PendingDetection[];
}

const props = defineProps<Props>();

function formatDateHeader(dateString: string | null): string {
    if (!dateString) return 'Unknown Date';
    const date = parseISO(dateString);

    if (isToday(date)) return 'Today';
    if (isYesterday(date)) return 'Yesterday';
    if (isThisWeek(date)) return format(date, 'EEEE');
    if (isThisMonth(date)) return format(date, 'MMMM d');
    return format(date, 'MMMM d, yyyy');
}

function formatRelativeDate(dateString: string | null): string {
    if (!dateString) return '';
    const date = parseISO(dateString);
    const days = differenceInDays(new Date(), date);

    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    if (days < 30) return `${Math.floor(days / 7)} weeks ago`;
    return format(date, 'MMM d, yyyy');
}

const groupedByDate = computed(() => {
    const groups: Record<string, PendingDetection[]> = {};

    props.pendingDetections.forEach((detection) => {
        if (!detection.created_at) {
            const key = 'Unknown Date';
            if (!groups[key]) groups[key] = [];
            groups[key].push(detection);
            return;
        }

        const date = parseISO(detection.created_at);
        const dateKey = format(date, 'yyyy-MM-dd');

        if (!groups[dateKey]) {
            groups[dateKey] = [];
        }
        groups[dateKey].push(detection);
    });

    return Object.keys(groups)
        .sort((a, b) => {
            if (a === 'Unknown Date') return 1;
            if (b === 'Unknown Date') return -1;
            return b.localeCompare(a);
        })
        .map((key) => ({
            date: key,
            dateLabel: formatDateHeader(groups[key][0]?.created_at || null),
            items: groups[key],
        }));
});
</script>

<template>
    <Modal v-slot="{ close }" position="top" max-width="3xl" panel-classes="bg-white rounded-lg p-6 dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <div class="space-y-6 tracking-tight">
            <div class="inline-flex items-baseline space-x-2">
                <AlertTriangle class="size-4 text-amber-600 dark:text-amber-400" />
                <div>
                    <h1 class="text-sm font-bold sm:text-lg">Pending Detections</h1>
                    <span class="text-xs text-muted-foreground sm:text-sm"> Activity links awaiting similarity detection </span>
                </div>
            </div>

            <Deferred data="pendingDetections">
                <template #fallback>
                    <div class="flex items-center justify-center py-12">
                        <div class="flex flex-col items-center gap-2">
                            <Loader class="animate-spin" />
                            <p class="text-sm text-muted-foreground">Loading pending detections...</p>
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {{ pendingDetections.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">Total Pending</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {{ groupedByDate.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">Dates</div>
                    </div>
                </div>

                <div class="min-h-0 flex-1 space-y-4">
                    <div v-if="pendingDetections.length > 0">
                        <ScrollArea class="h-[450px] rounded-md">
                            <div class="space-y-6">
                                <template v-for="(group, groupIndex) in groupedByDate" :key="group.date">
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2 px-2">
                                            <Calendar class="size-4 text-muted-foreground" />
                                            <h3 class="text-sm font-semibold text-foreground">
                                                {{ group.dateLabel }}
                                            </h3>
                                            <Badge variant="secondary" class="ml-auto text-xs">
                                                {{ group.items.length }}
                                            </Badge>
                                        </div>

                                        <ItemGroup>
                                            <div class="p-2">
                                                <template v-for="(link, index) in group.items" :key="link.id">
                                                    <Item as-child @click="close">
                                                        <Link :href="`/activities/${link.activity_id}/links/${link.id}`">
                                                            <ItemContent>
                                                                <ItemTitle class="text-sm font-bold">
                                                                    {{ link.course?.name || 'Course' }}
                                                                </ItemTitle>
                                                                <ItemDescription class="text-xs font-semibold">
                                                                    {{ link.activity }}
                                                                    <span v-if="link.language" class="font-extralight capitalize">
                                                                        - {{ link.language }}
                                                                    </span>
                                                                </ItemDescription>
                                                                <ItemDescription v-if="link.created_at" class="mt-1 text-xs text-muted-foreground">
                                                                    {{ formatRelativeDate(link.created_at) }}
                                                                </ItemDescription>
                                                            </ItemContent>
                                                            <ItemContent>
                                                                <Badge
                                                                    variant="outline"
                                                                    class="border-amber-600 text-xs text-amber-600 dark:border-amber-400 dark:text-amber-400"
                                                                >
                                                                    Pending
                                                                </Badge>
                                                            </ItemContent>
                                                        </Link>
                                                    </Item>
                                                    <ItemSeparator v-if="index < group.items.length - 1" />
                                                </template>
                                            </div>
                                        </ItemGroup>

                                        <Separator v-if="groupIndex < groupedByDate.length - 1" class="my-2" />
                                    </div>
                                </template>
                            </div>
                        </ScrollArea>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                        <CircleCheck class="mb-4 size-12 text-green-600 opacity-75 dark:text-green-400" />
                        <p class="text-sm font-medium">No pending detections</p>
                        <p class="mt-1 text-xs">All closed activity links have been processed</p>
                    </div>
                </div>
            </Deferred>
        </div>
    </Modal>
</template>
