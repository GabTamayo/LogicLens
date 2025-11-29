<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue'
import { Badge } from '@/components/ui/badge'
import Separator from '@/components/ui/separator/Separator.vue'
import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from '@/components/ui/resizable'
import { DetectionShowProps } from '@/types'
import { onMounted, watch, nextTick, ref, onUnmounted } from 'vue'
import Prism from 'prismjs'
import '../../../css/themes/prism-atom-dark.css'
import 'prismjs/plugins/line-numbers/prism-line-numbers.css'
import 'prismjs/plugins/line-numbers/prism-line-numbers'
import 'prismjs/components/prism-java'
import 'prismjs/components/prism-python'
import 'prismjs/plugins/line-highlight/prism-line-highlight'
import 'prismjs/plugins/line-highlight/prism-line-highlight.css'
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area'

const props = defineProps<DetectionShowProps>()
const isSmallScreen = ref(false)
const checkScreenSize = () => { isSmallScreen.value = window.innerWidth < 1024 }
const getLanguageFromExtension = (input?: string): string => {
    if (!input) return 'plaintext'
    const ext = input.toLowerCase()
    const languageMap: Record<string, string> = {
        java: 'java',
        py: 'python',
        python: 'python',
    }
    return languageMap[ext] || (ext || 'plaintext')
}
const formatLineMatches = (matches: any[], key: 'code_a' | 'code_b') => {
    if (!matches || matches.length === 0) return ''
    return matches.map(m => `${m[key][0]}-${m[key][1]}`).join(',')
}
const getSimilarityBorder = (score: number) => {
    if (score >= 0.90) {
        return 'border-red-500'
    }
    if (score >= 0.85) {
        return 'border-orange-500'
    }
    if (score >= 0.75) {
        return 'border-yellow-500'
    }
    return 'border-muted-foreground/40'
}


onMounted(() => {
    checkScreenSize()
    window.addEventListener('resize', checkScreenSize)

    nextTick(() => {
        setTimeout(() => Prism.highlightAll(), 0)
    })
})

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize)
})

watch(
    () => [props.fileA, props.fileB],
    () => {
        nextTick(() => {
            setTimeout(() => Prism.highlightAll(), 0)
        })
    },
    { deep: true }
)

watch(
    () => props.detection.line_matches,
    () => nextTick(() => Prism.highlightAll()),
    { deep: true }
)
</script>


<template>
    <div>
        <Modal max-width="7xl" panel-classes="bg-white rounded dark:bg-[hsl(222.2_84%_4.9%)]">
            <div class="flex flex-col h-screen gap-4 p-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg sm:text-2xl font-bold tracking-tight">Similarity Detection</h1>
                        <p class="text-xs sm:text-sm text-muted-foreground mt-1">Side-by-side code comparison</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="w-24 h-24 rounded-full border-4 flex items-center justify-center"
                            :class="getSimilarityBorder(props.detection.avg_score)">
                            <span class="text-xl sm:text-3xl font-black">
                                {{ Math.round(props.detection.avg_score * 100) }}%
                            </span>
                        </div>
                        <span class="text-xs text-muted-foreground font-medium">Similarity Score</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-8 rounded-lg bg-muted/50 p-4 *">
                    <div class="flex flex-col min-w-0">
                        <span
                            class="text-2xs sm:text-xs font-semibold text-muted-foreground uppercase tracking-tight">Submission
                            A</span>
                        <span class="text-2xs sm:text-xs font-medium mt-1 truncate">{{
                            props.detection.submission_a.student_name }}</span>
                        <span class="text-2xs sm:text-xs text-muted-foreground truncate">{{
                            props.detection.submission_a.student_no }}</span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-1 min-w-0">
                        <div class="text-2xs sm:text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                            Metrics</div>
                        <div class="text-2xs sm:text-xs space-y-1 text-center truncate">
                            <div>Sequence Score: <span class="font-semibold text-foreground">{{
                                Math.round(props.detection.seq_score * 100) }}%</span></div>
                            <div>Structure Score: <span class="font-semibold text-foreground">{{
                                Math.round(props.detection.struct_score * 100) }}%</span></div>
                        </div>
                    </div>
                    <div class="flex flex-col text-end min-w-0">
                        <span
                            class="text-2xs sm:text-xs font-semibold text-muted-foreground uppercase tracking-tight">Submission
                            B</span>
                        <span class="text-2xs sm:text-xs font-medium mt-1 truncate">{{
                            props.detection.submission_b.student_name }}</span>
                        <span class="text-2xs sm:text-xs text-muted-foreground truncate">{{
                            props.detection.submission_b.student_no }}</span>
                    </div>
                </div>

                <div class="flex-1 min-h-0 rounded-lg overflow-hidden border border-border">
                    <ResizablePanelGroup :direction="isSmallScreen ? 'vertical' : 'horizontal'"
                        class="bg-[#1d1f21] h-full truncate">
                        <ResizablePanel :default-size="50">
                            <div class="flex h-full flex-col">
                                <div class="px-4 py-2 bg-coal-900 border-b border-border/50">
                                    <span class="text-xs font-semibold text-white">Code A</span>
                                </div>
                                <ScrollArea class="flex-1 h-full">
                                    <pre class="line-numbers p-4 text-xs"
                                        :data-line="formatLineMatches(props.detection.line_matches, 'code_a')"><code :class="`language-${getLanguageFromExtension(props.detection.submission_a.language?.split('.').pop())}`">{{ props.fileA }}</code></pre>
                                    <ScrollBar orientation="horizontal" />
                                </ScrollArea>
                            </div>
                        </ResizablePanel>
                        <ResizableHandle />
                        <ResizablePanel :default-size="50">
                            <div class="flex h-full flex-col">
                                <div class="px-4 py-2 bg-coal-900 border-b border-border/50">
                                    <span class="text-xs font-semibold text-white">Code B</span>
                                </div>
                                <ScrollArea class="flex-1 h-full">
                                    <pre class="line-numbers p-4 text-xs"
                                        :data-line="formatLineMatches(props.detection.line_matches, 'code_b')"><code :class="`language-${getLanguageFromExtension(props.detection.submission_b.language?.split('.').pop())}`">{{ props.fileB }}</code></pre>
                                    <ScrollBar orientation="horizontal" />
                                </ScrollArea>
                            </div>
                        </ResizablePanel>
                    </ResizablePanelGroup>
                </div>
            </div>
        </Modal>
    </div>
</template>
