<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useLanguage } from '@/composables/useLanguage';
import type { CourseActivityLink } from '@/types';
import { Circle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    activities: CourseActivityLink[];
    selectedActivityId?: string;
}>();

const emit = defineEmits<{
    select: [activity: CourseActivityLink];
}>();

const { getLanguageConfig } = useLanguage();

const openActivities = computed(() => props.activities.filter((activity) => activity.is_open));

const closedActivities = computed(() => props.activities.filter((activity) => !activity.is_open));
</script>

<template>
    <ScrollArea class="h-full">
        <Accordion type="multiple" :default-value="['open']" class="w-full p-4">
            <AccordionItem value="open">
                <AccordionTrigger class="text-sm font-semibold">
                    <div class="flex items-center gap-2">
                        <Circle class="h-2 w-2 shrink-0 fill-current text-green-500" />
                        <span>Open Activities ({{ openActivities.length }})</span>
                    </div>
                </AccordionTrigger>
                <AccordionContent>
                    <div class="flex flex-col gap-1 pt-2">
                        <div
                            v-for="activity in openActivities"
                            :key="activity.id"
                            @click="emit('select', activity)"
                            :class="[
                                'group flex cursor-pointer flex-col gap-2 rounded-lg border p-3 transition-all hover:bg-accent',
                                selectedActivityId === activity.id ? 'border-primary bg-accent' : 'border-border',
                            ]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="line-clamp-2 text-sm leading-tight font-semibold">
                                    {{ activity.activity_title }}
                                </h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    :class="['flex items-center gap-1.5 text-xs', getLanguageConfig(activity.activity_language).colors]"
                                >
                                    <img
                                        v-if="getLanguageConfig(activity.activity_language).logo"
                                        :src="getLanguageConfig(activity.activity_language).logo ?? ''"
                                        :alt="activity.activity_language"
                                        class="h-3 w-3 object-contain"
                                    />
                                    <span>{{ activity.activity_language }}</span>
                                </Badge>
                            </div>
                        </div>
                        <div v-if="openActivities.length === 0" class="py-8 text-center text-sm text-muted-foreground">No open activities</div>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <AccordionItem value="closed">
                <AccordionTrigger class="text-sm font-semibold">
                    <div class="flex items-center gap-2">
                        <Circle class="h-2 w-2 shrink-0 fill-current text-red-500" />
                        <span>Closed Activities ({{ closedActivities.length }})</span>
                    </div>
                </AccordionTrigger>
                <AccordionContent>
                    <div class="flex flex-col gap-1 pt-2">
                        <div
                            v-for="activity in closedActivities"
                            :key="activity.id"
                            @click="emit('select', activity)"
                            :class="[
                                'group flex cursor-pointer flex-col gap-2 rounded-lg border p-3 transition-all hover:bg-accent',
                                selectedActivityId === activity.id ? 'border-primary bg-accent' : 'border-border',
                            ]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="line-clamp-2 text-sm leading-tight font-semibold">
                                    {{ activity.activity_title }}
                                </h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    :class="['flex items-center gap-1.5 text-xs', getLanguageConfig(activity.activity_language).colors]"
                                >
                                    <img
                                        v-if="getLanguageConfig(activity.activity_language).logo"
                                        :src="getLanguageConfig(activity.activity_language).logo ?? ''"
                                        :alt="activity.activity_language"
                                        class="h-3 w-3 object-contain"
                                    />
                                    <span>{{ activity.activity_language }}</span>
                                </Badge>
                            </div>
                        </div>
                        <div v-if="closedActivities.length === 0" class="py-8 text-center text-sm text-muted-foreground">No closed activities</div>
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </ScrollArea>
</template>
