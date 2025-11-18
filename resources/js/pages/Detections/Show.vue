<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue'
import { Badge } from '@/components/ui/badge'
import Separator from '@/components/ui/separator/Separator.vue'
import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from '@/components/ui/resizable'
import { DetectionShowProps } from '@/types'
import { onMounted, watch, h } from 'vue'
import Prism from 'prismjs'
import 'prismjs/themes/prism-tomorrow.css'
import 'prismjs/plugins/line-numbers/prism-line-numbers.css'
import 'prismjs/plugins/line-numbers/prism-line-numbers'
import 'prismjs/components/prism-java'
import 'prismjs/components/prism-python'
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue'

const props = defineProps<DetectionShowProps>()

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
const getSimilarityBadge = (score: number) => {
    if (score >= 0.90) {
        return h(Badge, { variant: 'destructive' }, () => 'Very High')
    }
    if (score >= 0.85) {
        return h(Badge, { variant: 'customOrange' }, () => 'High')
    }
    if (score >= 0.75) {
        return h(Badge, { variant: 'customYellow' }, () => 'Moderate')
    }
    return h(Badge, { variant: 'outline' }, () => 'Low')
}

onMounted(() => {
    setTimeout(() => Prism.highlightAll(), 50)
})

watch(
    () => [props.fileA, props.fileB],
    () => setTimeout(() => Prism.highlightAll(), 100),
    { deep: true }
)
</script>


<template>
    <div>
        <Modal class="bg-white rounded dark:bg-[hsl(222.2_84%_4.9%)]" max-width="7xl"
            panel-classes="bg-white rounded dark:bg-[hsl(222.2_84%_4.9%)]" position="top">
            <div class="flex justify-between mb-4">
                <div class="text-2xl font-bold tracking-tight">
                    <h1>Comparison Details</h1>
                </div>
                <div class="flex items-center mr-8">
                    <p class="text-xl font-bold tracking-tight">{{ Math.round(props.detection.similarity_score * 100)
                        }}%</p>
                    <Separator orientation="vertical" class="mx-4 h-8" />
                    <component :is="getSimilarityBadge(props.detection.similarity_score)" />
                </div>
            </div>

            <div class="flex justify-between mb-6 items-start tracking-tight">
                <div class="flex flex-col text-start">
                    <span class="font-medium">{{ props.detection.submission_a.student_name }}</span>
                    <span class="text-xs text-muted-foreground font-light">{{ props.detection.submission_a.student_no
                        }}</span>
                </div>
                <div class="flex flex-col text-end">
                    <span class="font-medium">{{ props.detection.submission_b.student_name }}</span>
                    <span class="text-xs text-muted-foreground font-light">{{ props.detection.submission_b.student_no
                        }}</span>
                </div>
            </div>

            <ResizablePanelGroup direction="horizontal" class="bg-neutral-800 border rounded-md tracking-tight">
                <ResizablePanel :default-size="50">
                    <div class="h-180">
                        <ScrollArea class="h-full">
                            <div class="overflow-x-auto text-xs">
                                <pre
                                    class="line-numbers p-4"><code :class="`language-${getLanguageFromExtension(props.detection.submission_a.language?.split('.').pop())}`">{{ props.fileA }}</code></pre>
                            </div>
                        </ScrollArea>
                    </div>
                </ResizablePanel>
                <ResizableHandle />
                <ResizablePanel :default-size="50">
                    <div class="h-180">
                        <ScrollArea class="h-full">
                            <div class="overflow-x-auto text-xs">
                                <pre
                                    class="line-numbers p-4"><code :class="`language-${getLanguageFromExtension(props.detection.submission_b.language?.split('.').pop())}`">{{ props.fileB }}</code></pre>
                            </div>
                        </ScrollArea>
                    </div>
                </ResizablePanel>
            </ResizablePanelGroup>
        </Modal>
    </div>
</template>
