<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { AlertCircle, AlertTriangle, CheckIcon, Clock, Keyboard, Monitor, MousePointer } from 'lucide-vue-next';
import axios from 'axios';

interface Violation {
    id: number;
    type: string;
    details: string;
    timestamp: string;
    ip_address: string;
}

interface ViolationsDialogProps {
    submissionId: string;
    studentName: string;
}

const props = defineProps<ViolationsDialogProps>();
const modelValue = defineModel<boolean>();

const violations = ref<Violation[]>([]);
const isLoading = ref(false);
const totalViolations = ref(0);

const violationTypeConfig: Record<string, { icon: any; label: string; color: string; bgColor: string }> = {
    tab_switch: {
        icon: Monitor,
        label: 'Tab Switch',
        color: 'text-orange-600 dark:text-orange-400',
        bgColor: 'bg-orange-100 dark:bg-orange-950',
    },
    fullscreen_exit: {
        icon: Monitor,
        label: 'Fullscreen Exit',
        color: 'text-red-600 dark:text-red-400',
        bgColor: 'bg-red-100 dark:bg-red-950',
    },
    keyboard_shortcut: {
        icon: Keyboard,
        label: 'Keyboard Shortcut',
        color: 'text-yellow-600 dark:text-yellow-400',
        bgColor: 'bg-yellow-100 dark:bg-yellow-950',
    },
    context_menu: {
        icon: MousePointer,
        label: 'Context Menu',
        color: 'text-blue-600 dark:text-blue-400',
        bgColor: 'bg-blue-100 dark:bg-blue-950',
    },
    window_blur: {
        icon: AlertTriangle,
        label: 'Window Focus Lost',
        color: 'text-purple-600 dark:text-purple-400',
        bgColor: 'bg-purple-100 dark:bg-purple-950',
    },
};

const fetchViolations = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/submissions/${props.submissionId}/violations`);
        violations.value = response.data.violations;
        totalViolations.value = response.data.total;
    } catch (error) {
        console.error('Failed to fetch violations:', error);
    } finally {
        isLoading.value = false;
    }
};

const getViolationConfig = (type: string) => {
    return violationTypeConfig[type] || {
        icon: AlertCircle,
        label: type,
        color: 'text-gray-600 dark:text-gray-400',
        bgColor: 'bg-gray-100 dark:bg-gray-950',
    };
};

// Watch for dialog open to fetch violations
watch(modelValue, (newValue) => {
    if (newValue) {
        fetchViolations();
    }
});
</script>

<template>
    <Dialog v-model:open="modelValue">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <AlertCircle class="h-5 w-5 text-orange-500" />
                    Exam Violations
                </DialogTitle>
                <DialogDescription>
                    Security violations detected for {{ studentName }}'s submission
                </DialogDescription>
            </DialogHeader>

            <div v-if="isLoading" class="flex items-center justify-center py-12">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <div class="h-5 w-5 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                    <span>Loading violations...</span>
                </div>
            </div>

            <div v-else-if="violations.length === 0" class="flex flex-col items-center justify-center gap-3 py-12">
                <div class="rounded-full bg-green-100 p-3 dark:bg-green-950">
                    <CheckIcon class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <div class="text-center">
                    <p class="font-medium">No Violations Detected</p>
                    <p class="text-sm text-muted-foreground">This student had a clean activity session</p>
                </div>
            </div>

            <div v-else class="space-y-4">
                <!-- Summary -->
                <div class="flex items-center justify-between rounded-lg border bg-muted/50 p-4">
                    <div>
                        <p class="text-sm font-medium">Total Violations</p>
                        <p class="text-2xl font-bold">{{ totalViolations }}</p>
                    </div>
                    <AlertTriangle class="h-8 w-8 text-orange-500" />
                </div>

                <!-- Violations List -->
                <ScrollArea class="h-[400px] rounded-lg border">
                    <div class="space-y-3 p-4">
                        <div
                            v-for="(violation, index) in violations"
                            :key="violation.id"
                            class="rounded-lg border bg-card p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-1 rounded-full p-2"
                                    :class="getViolationConfig(violation.type).bgColor"
                                >
                                    <component
                                        :is="getViolationConfig(violation.type).icon"
                                        class="h-4 w-4"
                                        :class="getViolationConfig(violation.type).color"
                                    />
                                </div>

                                <div class="flex-1 space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <Badge variant="outline" class="mb-1">
                                                {{ getViolationConfig(violation.type).label }}
                                            </Badge>
                                            <p class="text-sm text-muted-foreground">
                                                {{ violation.details }}
                                            </p>
                                        </div>
                                    </div>

                                    <Separator />

                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <Clock class="h-3 w-3" />
                                            {{ violation.timestamp }}
                                        </div>
                                        <div v-if="violation.ip_address" class="flex items-center gap-1">
                                            <Monitor class="h-3 w-3" />
                                            {{ violation.ip_address }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </ScrollArea>
            </div>
        </DialogContent>
    </Dialog>
</template>