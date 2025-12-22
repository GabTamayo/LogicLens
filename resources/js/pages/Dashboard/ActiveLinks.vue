<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Item, ItemContent, ItemDescription, ItemGroup, ItemSeparator, ItemTitle } from '@/components/ui/item';
import { ScrollArea } from '@/components/ui/scroll-area';
import Separator from '@/components/ui/separator/Separator.vue';
import type { ActiveLink } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Deferred, Modal } from '@inertiaui/modal-vue';
import { differenceInDays, formatDistanceToNow, parseISO } from 'date-fns';
import { Calendar, CalendarOff, Circle, Clock, Ellipsis, Loader } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    activeLinks: ActiveLink[];
}

const props = defineProps<Props>();

const linksWithDeadline = computed(() => props.activeLinks.filter((link) => link.has_deadline));

const linksWithoutDeadline = computed(() => props.activeLinks.filter((link) => !link.has_deadline));

function formatRelativeDeadline(expires_at: string | null) {
    if (!expires_at) return 'No deadline';
    const date = parseISO(expires_at);
    return formatDistanceToNow(date, { addSuffix: true });
}

function formatDate(dateString: string | null) {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString();
}

function formatRelativeDate(dateString: string | null): string {
    if (!dateString) return '';
    const date = parseISO(dateString);
    return formatDistanceToNow(date, { addSuffix: true });
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
        case 'overdue':
            return 'text-red-600 dark:text-red-400';
        case 'urgent':
            return 'text-orange-600 dark:text-orange-400';
        case 'soon':
            return 'text-yellow-600 dark:text-yellow-400';
        default:
            return 'text-muted-foreground';
    }
}
</script>

<template>
    <Modal v-slot="{ close }" position="top" max-width="3xl" panel-classes="bg-white rounded-lg p-6 dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <div class="space-y-6 tracking-tight">
            <div class="inline-flex items-baseline space-x-2">
                <Circle class="size-3 text-green-600" :stroke="'none'" :fill="'currentColor'" />
                <div>
                    <h1 class="text-sm font-bold sm:text-lg">Active Links</h1>
                    <span class="text-xs text-muted-foreground sm:text-sm">All your currently active activity links</span>
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

                <div class="grid grid-cols-3 gap-4 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900/50 dark:bg-green-950/20">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ activeLinks.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">Total Active</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ linksWithDeadline.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">With Deadline</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-muted-foreground">
                            {{ linksWithoutDeadline.length }}
                        </div>
                        <div class="text-xs text-muted-foreground">No Deadline</div>
                    </div>
                </div>

                <div class="min-h-0 flex-1 space-y-4">
                    <div v-if="linksWithDeadline.length > 0">
                        <div class="mb-3 flex items-center gap-2">
                            <Calendar class="size-4 text-primary" />
                            <h3 class="text-sm font-semibold">With Deadline</h3>
                            <Badge variant="secondary" class="ml-auto">
                                {{ linksWithDeadline.length }}
                            </Badge>
                        </div>
                        <ScrollArea class="h-[180px] rounded-md">
                            <ItemGroup>
                                <div class="p-2">
                                    <template v-for="(link, index) in linksWithDeadline" :key="link.id">
                                        <Item as-child @click="close">
                                            <Link :href="`/activities/${link.activity_id}/links/${link.id}`">
                                                <ItemContent>
                                                    <ItemTitle class="text-sm font-bold">
                                                        {{ link.course?.name || 'Course' }}
                                                    </ItemTitle>
                                                    <ItemDescription class="text-xs font-semibold">
                                                        {{ link.activity }}
                                                        <span class="font-extralight capitalize"> - {{ link.language }} </span>
                                                    </ItemDescription>
                                                    <ItemDescription v-if="link.created_at" class="mt-1 text-xs text-muted-foreground">
                                                        {{ formatRelativeDate(link.created_at) }}
                                                    </ItemDescription>
                                                </ItemContent>
                                                <ItemContent class="relative">
                                                    <div class="space-y-1 text-end">
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            <Clock :class="`size-3 ${getUrgencyColor(getDeadlineUrgency(link.expires_at))}`" />
                                                            <ItemDescription class="text-xs font-medium">
                                                                {{ formatDate(link.expires_at) }}
                                                            </ItemDescription>
                                                        </div>
                                                        <ItemDescription
                                                            :class="`text-xs font-medium ${getUrgencyColor(getDeadlineUrgency(link.expires_at))}`"
                                                        >
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

                    <Separator v-if="linksWithoutDeadline.length > 0 && linksWithDeadline.length > 0" class="my-4" />

                    <div v-if="linksWithoutDeadline.length > 0">
                        <div class="mb-3 flex items-center gap-2">
                            <CalendarOff class="size-4 text-destructive" />
                            <h3 class="text-sm font-semibold">No Deadline</h3>
                            <Badge variant="secondary" class="ml-auto">
                                {{ linksWithoutDeadline.length }}
                            </Badge>
                        </div>
                        <ScrollArea class="h-[180px] rounded-md">
                            <ItemGroup>
                                <div class="p-2">
                                    <template v-for="(link, index) in linksWithoutDeadline" :key="link.id">
                                        <Item as-child @click="close">
                                            <Link :href="`/activities/${link.activity_id}/links/${link.id}`">
                                                <ItemContent>
                                                    <ItemTitle class="text-sm font-bold">
                                                        {{ link.course?.name || 'Course' }}
                                                    </ItemTitle>
                                                    <ItemDescription class="text-xs font-semibold">
                                                        {{ link.activity }}
                                                        <span class="font-extralight capitalize"> - {{ link.language }} </span>
                                                    </ItemDescription>
                                                    <ItemDescription v-if="link.created_at" class="mt-1 text-xs text-muted-foreground">
                                                        {{ formatRelativeDate(link.created_at) }}
                                                    </ItemDescription>
                                                </ItemContent>
                                                <ItemContent>
                                                    <Badge variant="outline" class="text-xs"> No deadline </Badge>
                                                </ItemContent>
                                            </Link>
                                        </Item>
                                        <ItemSeparator v-if="index < linksWithoutDeadline.length - 1" />
                                    </template>
                                </div>
                            </ItemGroup>
                        </ScrollArea>
                    </div>

                    <div v-if="activeLinks.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                        <Ellipsis class="mb-4 size-12 animate-pulse opacity-20" />
                        <p class="text-sm font-medium">No active links</p>
                        <p class="mt-1 text-xs">All activity links are currently closed</p>
                    </div>
                </div>
            </Deferred>
        </div>
    </Modal>
</template>
