<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from '@/components/ui/resizable';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { useSimilarity } from '@/composables/useSimilarity';
import { DetectionShowProps } from '@/types';
import { Modal } from '@inertiaui/modal-vue';
import { Code2 } from 'lucide-vue-next';
import Prism from 'prismjs';
import 'prismjs/components/prism-java';
import 'prismjs/components/prism-python';
import 'prismjs/plugins/line-highlight/prism-line-highlight';
import 'prismjs/plugins/line-highlight/prism-line-highlight.css';
import 'prismjs/plugins/line-numbers/prism-line-numbers';
import 'prismjs/plugins/line-numbers/prism-line-numbers.css';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import '../../../css/themes/prism-atom-dark.css';

const props = defineProps<DetectionShowProps>();
const { getSimilarityBorder, getSimilarityTextColor } = useSimilarity();
const isSmallScreen = ref(false);
const checkScreenSize = () => {
    isSmallScreen.value = window.innerWidth < 1024;
};
const getLanguageFromExtension = (input?: string): string => {
    if (!input) return 'plaintext';
    const ext = input.toLowerCase();
    const languageMap: Record<string, string> = {
        java: 'java',
        py: 'python',
        python: 'python',
    };
    return languageMap[ext] || ext || 'plaintext';
};
const formatLineMatches = (matches: any[], key: 'code_a' | 'code_b') => {
    if (!matches || matches.length === 0) return '';
    return matches.map((m) => `${m[key][0]}-${m[key][1]}`).join(',');
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);

    nextTick(() => {
        setTimeout(() => Prism.highlightAll(), 0);
    });
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});

watch(
    () => [props.fileA, props.fileB],
    () => {
        nextTick(() => {
            setTimeout(() => Prism.highlightAll(), 0);
        });
    },
    { deep: true },
);

watch(
    () => props.detection.line_matches,
    () => nextTick(() => Prism.highlightAll()),
    { deep: true },
);
</script>

<template>
    <div>
        <Modal max-width="7xl" panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
            <div class="flex h-[90vh] flex-col">
                <!-- Fixed Header Section -->
                <div class="space-y-4 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-lg font-bold tracking-tight sm:text-2xl">Similarity Detection</h1>
                            <p class="mt-1 text-xs text-muted-foreground sm:text-sm">Side-by-side code comparison</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div
                                class="flex h-24 w-24 items-center justify-center rounded-full border-4"
                                :class="[getSimilarityBorder(props.detection.avg_score), getSimilarityTextColor(props.detection.avg_score)]"
                            >
                                <span class="text-xl font-black sm:text-3xl"> {{ Math.round(props.detection.avg_score * 100) }}% </span>
                            </div>
                            <span class="text-xs font-medium text-muted-foreground">Similarity Score</span>
                        </div>
                    </div>

                    <Card>
                        <div class="grid grid-cols-3 gap-6 rounded-lg px-4">
                            <div class="flex min-w-0 flex-col">
                                <span class="text-2xs font-semibold tracking-tight text-muted-foreground uppercase sm:text-xs">Submission A</span>
                                <span class="mt-1 truncate text-2xs font-medium sm:text-xs">{{ props.detection.submission_a.student_name }}</span>
                            </div>

                            <div class="flex min-w-0 flex-col items-center justify-center gap-1">
                                <div class="text-2xs font-semibold tracking-tight text-muted-foreground uppercase sm:text-xs">Metrics</div>
                                <div class="space-y-0.5 truncate text-center text-2xs sm:text-xs">
                                    <div>
                                        Sequence Score:
                                        <span class="font-semibold text-foreground">{{ Math.round(props.detection.seq_score * 100) }}%</span>
                                    </div>
                                    <div>
                                        Structure Score:
                                        <span class="font-semibold text-foreground">{{ Math.round(props.detection.struct_score * 100) }}%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex min-w-0 flex-col text-end">
                                <span class="text-2xs font-semibold tracking-tight text-muted-foreground uppercase sm:text-xs">Submission B</span>
                                <span class="mt-1 truncate text-2xs font-medium sm:text-xs">{{ props.detection.submission_b.student_name }}</span>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Scrollable Code Comparison Section -->
                <div class="min-h-0 flex-1 overflow-hidden px-4 pb-4">
                    <div class="h-full overflow-hidden rounded-lg border border-border">
                        <ResizablePanelGroup :direction="isSmallScreen ? 'vertical' : 'horizontal'" class="h-full truncate bg-[#1d1f21]">
                            <ResizablePanel :default-size="50">
                                <div class="flex h-full flex-col">
                                    <div class="bg-coal-900 flex items-center gap-2 border-b border-border/50 px-4 py-2">
                                        <Code2 class="h-4 w-4 text-blue-400" />
                                        <span class="text-xs font-semibold text-white">Code A</span>
                                    </div>
                                    <ScrollArea class="h-full flex-1">
                                        <pre
                                            class="line-numbers p-4 text-xs"
                                            :data-line="formatLineMatches(props.detection.line_matches, 'code_a')"
                                        ><code :class="`language-${getLanguageFromExtension(props.detection.submission_a.language?.split('.').pop())}`">{{ props.fileA }}</code></pre>
                                        <ScrollBar orientation="horizontal" />
                                    </ScrollArea>
                                </div>
                            </ResizablePanel>
                            <ResizableHandle />
                            <ResizablePanel :default-size="50">
                                <div class="flex h-full flex-col">
                                    <div class="bg-coal-900 flex items-center gap-2 border-b border-border/50 px-4 py-2">
                                        <Code2 class="h-4 w-4 text-purple-400" />
                                        <span class="text-xs font-semibold text-white">Code B</span>
                                    </div>
                                    <ScrollArea class="h-full flex-1">
                                        <pre
                                            class="line-numbers p-4 text-xs"
                                            :data-line="formatLineMatches(props.detection.line_matches, 'code_b')"
                                        ><code :class="`language-${getLanguageFromExtension(props.detection.submission_b.language?.split('.').pop())}`">{{ props.fileB }}</code></pre>
                                        <ScrollBar orientation="horizontal" />
                                    </ScrollArea>
                                </div>
                            </ResizablePanel>
                        </ResizablePanelGroup>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>
