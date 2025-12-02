<script setup lang="ts">
import { computed } from 'vue';
import { Deferred, Modal } from '@inertiaui/modal-vue';
import { Circle, Ellipsis, Loader } from 'lucide-vue-next';
import Separator from '@/components/ui/separator/Separator.vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, CalendarOff, Clock } from 'lucide-vue-next';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Item, ItemContent, ItemDescription, ItemTitle, ItemGroup, ItemSeparator, ItemActions } from '@/components/ui/item';
import { Badge } from '@/components/ui/badge';
import Button from '@/components/ui/button/Button.vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { formatDistanceToNow, parseISO, differenceInDays } from 'date-fns';
import type { ActiveLink } from '@/types';

interface Props {
    activeLinks: ActiveLink[];
}

const props = defineProps<Props>();

const linksWithDeadline = computed(() =>
    props.activeLinks.filter(link => link.has_deadline)
);

const linksWithoutDeadline = computed(() =>
    props.activeLinks.filter(link => !link.has_deadline)
);

function formatRelativeDeadline(expires_at: string | null) {
    if (!expires_at) return 'No deadline';
    const date = parseISO(expires_at);
    return formatDistanceToNow(date, { addSuffix: true });
}

function formatDate(dateString: string | null) {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString();
}

function getDeadlineUrgency(expires_at: string | null) {
    if (!expires_at) return 'none';
    const days = differenceInDays(parseISO(expires_at), new Date());
    if (days < 0) return 'overdue';
    if (days <= 2) return 'urgent';
    if (days <= 7) return 'soon';
    return 'normal';
}

function getUrgencyColor(urgency: string) {
    switch (urgency) {
        case 'overdue': return 'text-red-600 dark:text-red-400';
        case 'urgent': return 'text-orange-600 dark:text-orange-400';
        case 'soon': return 'text-yellow-600 dark:text-yellow-400';
        default: return 'text-muted-foreground';
    }
}
</script>

<template>
    <Modal v-slot="{ close }" position="top" max-width="3xl"
        panel-classes="bg-white rounded-lg p-6 dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <div class="tracking-tight space-y-6">

            <!-- Header -->
            <div class="inline-flex items-baseline space-x-2">
                <Circle class="size-3 text-green-600" :stroke="'none'" :fill="'currentColor'" />
                <div>
                    <h1 class="text-sm sm:text-lg font-bold">Active Links</h1>
                    <span class="text-xs sm:text-sm text-muted-foreground">All your currently active activity
                        links</span>
                </div>
            </div>

            <Deferred data="activeLinks">
                <template #fallback>
                    <div class="flex items-center justify-center py-12">
                        <div class="flex flex-col items-center gap-2">
                            <Loader class="animate-spin" />
                            <p class="text-sm text-muted-foreground">Loading active links...</p>
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-3 gap-4 rounded-lg p-4">
                    <div class="text-center  bg-muted p-1 rounded-xl">
                        <div class="text-2xl font-bold">{{ activeLinks.length }}</div>
                        <div class="text-xs text-muted-foreground">Total Active</div>
                    </div>
                    <div class="text-center bg-blue-50 dark:bg-blue-950/30 p-1 rounded-xl">
                        <div class="text-2xl text-blue-600 dark:text-blue-400 font-bold">{{ linksWithDeadline.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">With Deadline</div>
                    </div>
                    <div class="text-center bg-red-50 dark:bg-red-950/30 p-1 rounded-xl">
                        <div class="text-2xl text-red-600 dark:text-red-400 font-bold">{{ linksWithoutDeadline.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">No Deadline</div>
                    </div>
                </div>

                <!-- Links List -->
                <div class="flex-1 min-h-0 space-y-4">
                    <!-- With Deadline Section -->
                    <div v-if="linksWithDeadline.length > 0">
                        <div class="flex items-center gap-2 mb-3">
                            <Calendar class="size-4 text-primary" />
                            <h3 class="font-semibold text-sm">With Deadline</h3>
                            <Badge variant="secondary" class="ml-auto">
                                {{ linksWithDeadline.length }}
                            </Badge>
                        </div>
                        <ScrollArea class="h-[180px] rounded-md">
                            <ItemGroup>
                                <div class="p-2">
                                    <template v-for="(link, index) in linksWithDeadline" :key="link.id">
                                        <Item as-child @click="close">
                                            <Link :href="`/activities/${link.activity_id}`">
                                            <ItemContent>
                                                <ItemTitle class="text-sm font-bold">
                                                    {{ link.name }}
                                                </ItemTitle>
                                                <ItemDescription class="text-xs font-semibold">
                                                    {{ link.activity }}
                                                    <span class="font-extralight capitalize">
                                                        - {{ link.language }}
                                                    </span>
                                                </ItemDescription>
                                            </ItemContent>
                                            <ItemContent class="relative">
                                                <div class="text-end space-y-1">
                                                    <div class="flex items-center gap-1.5 justify-end">
                                                        <Clock
                                                            :class="`size-3 ${getUrgencyColor(getDeadlineUrgency(link.expires_at))}`" />
                                                        <ItemDescription class="text-xs font-medium">
                                                            {{ formatDate(link.expires_at) }}
                                                        </ItemDescription>
                                                    </div>
                                                    <ItemDescription
                                                        :class="`text-xs font-medium ${getUrgencyColor(getDeadlineUrgency(link.expires_at))}`">
                                                        {{ formatRelativeDeadline(link.expires_at) }}
                                                    </ItemDescription>
                                                </div>
                                            </ItemContent>
                                            </Link>
                                        </Item>
                                        <ItemSeparator v-if="index < linksWithDeadline.length - 1" />
                                    </template>
                                </div>
                            </ItemGroup>
                        </ScrollArea>
                    </div>

                    <Separator v-if="linksWithoutDeadline.length && linksWithDeadline.length > 0" class="my-4" />

                    <div v-if="linksWithoutDeadline.length > 0">
                        <div class="flex items-center gap-2 mb-3">
                            <CalendarOff class="size-4 text-destructive" />
                            <h3 class="font-semibold text-sm">No Deadline</h3>
                            <Badge variant="secondary" class="ml-auto">
                                {{ linksWithoutDeadline.length }}
                            </Badge>
                        </div>
                        <ScrollArea class="h-[180px] rounded-md">
                            <ItemGroup>
                                <div class="p-2">
                                    <template v-for="(link, index) in linksWithoutDeadline" :key="link.id">
                                        <Item as-child @click="close">
                                            <Link :href="`/activities/${link.activity_id}`">
                                            <ItemContent>
                                                <ItemTitle class="text-sm font-bold">
                                                    {{ link.name }}
                                                </ItemTitle>
                                                <ItemDescription class="text-xs font-semibold">
                                                    {{ link.activity }}
                                                    <span class="font-extralight capitalize">
                                                        - {{ link.language }}
                                                    </span>
                                                </ItemDescription>
                                            </ItemContent>
                                            <ItemContent>
                                                <Badge variant="outline" class="text-xs">
                                                    No deadline
                                                </Badge>
                                            </ItemContent>
                                            </Link>
                                        </Item>
                                        <ItemSeparator v-if="index < linksWithoutDeadline.length - 1" />
                                    </template>
                                </div>
                            </ItemGroup>
                        </ScrollArea>
                    </div>

                    <!-- Empty State -->
                    <div v-else
                        class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                        <Ellipsis class="size-12 mb-4 opacity-20 animate-pulse" />
                        <p class="text-sm font-medium">No active links</p>
                        <p class="text-xs mt-1">All activity links are currently closed</p>
                    </div>
                </div>
            </Deferred>

        </div>
    </Modal>
</template>
