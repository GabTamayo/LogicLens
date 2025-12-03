<script setup lang="ts">
import { Deferred, Modal } from '@inertiaui/modal-vue';
import { CircleCheck, Loader } from 'lucide-vue-next';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Item, ItemContent, ItemDescription, ItemTitle, ItemGroup, ItemSeparator } from '@/components/ui/item';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/vue3';

interface PendingDetection {
    id: string;
    activity_id: string;
    activity: string;
    language: string | null;
    name: string;
}

interface Props {
    pendingDetections: PendingDetection[];
}

defineProps<Props>();
</script>

<template>
    <Modal v-slot="{ close }" position="top" max-width="3xl"
        panel-classes="bg-white rounded-lg p-6 dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <div class="tracking-tight space-y-6">

            <!-- Header -->
            <div class="inline-flex items-baseline space-x-2">
                <AlertTriangle class="size-4 text-amber-600 dark:text-amber-400" />
                <div>
                    <h1 class="text-sm sm:text-lg font-bold">Pending Detections</h1>
                    <span class="text-xs sm:text-sm text-muted-foreground">
                        Activity links awaiting plagiarism detection
                    </span>
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

                <div class="text-center">
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                        {{ pendingDetections.length }}
                    </div>
                    <div class="text-xs text-muted-foreground">Needs Processing</div>
                </div>

                <!-- Pending List -->
                <div class="flex-1 min-h-0 space-y-4">
                    <div v-if="pendingDetections.length > 0">
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="font-semibold text-sm">Links Without Detections</h3>
                            <Badge variant="secondary" class="ml-auto">
                                {{ pendingDetections.length }}
                            </Badge>
                        </div>

                        <ScrollArea class="h-[400px] rounded-md">
                            <ItemGroup>
                                <div class="p-2">
                                    <template v-for="(link, index) in pendingDetections" :key="link.id">
                                        <Item as-child @click="close">
                                            <Link :href="`/activities/${link.activity_id}/links/${link.id}`">
                                            <ItemContent>
                                                <ItemTitle class="text-sm font-bold">
                                                    {{ link.name }}
                                                </ItemTitle>
                                                <ItemDescription class="text-xs font-semibold">
                                                    {{ link.activity }}
                                                    <span v-if="link.language" class="font-extralight capitalize">
                                                        - {{ link.language }}
                                                    </span>
                                                </ItemDescription>
                                            </ItemContent>
                                            <ItemContent>
                                                <Badge variant="outline"
                                                    class="text-xs text-amber-600 dark:text-amber-400 border-amber-600">
                                                    Pending
                                                </Badge>
                                            </ItemContent>
                                            </Link>
                                        </Item>
                                        <ItemSeparator v-if="index < pendingDetections.length - 1" />
                                    </template>
                                </div>
                            </ItemGroup>
                        </ScrollArea>
                    </div>

                    <!-- Empty State -->
                    <div v-else
                        class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                        <CircleCheck class="size-12 mb-4 opacity-75 text-green-600" />
                        <p class="text-sm font-medium">No pending detections</p>
                        <p class="text-xs mt-1">All closed activity links have been processed</p>
                    </div>
                </div>
            </Deferred>

        </div>
    </Modal>
</template>
