<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue'
import { Badge } from '@/components/ui/badge'
import Separator from '@/components/ui/separator/Separator.vue'
import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from '@/components/ui/resizable'
import { DetectionShowProps } from '@/types'
import { onMounted, watch, h, nextTick } from 'vue'
import Prism from 'prismjs'
import '../../../css/themes/prism-atom-dark.css'
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
    const percentage = Math.round(score * 100) + '%'
    let variant = 'outline'
    let label = 'Low'

    if (score >= 0.90) {
        variant = 'destructive'
        label = 'Very High'
    } else if (score >= 0.85) {
        variant = 'customOrange'
        label = 'High'
    } else if (score >= 0.75) {
        variant = 'customYellow'
        label = 'Moderate'
    }

    return h(
        Badge,
        { variant },
        () => `${percentage} ${label}`
    )
}


onMounted(() => {
    nextTick(() => {
        setTimeout(() => Prism.highlightAll(), 0)
    })
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

</script>


<template>
    <div>
        <Modal class="bg-white rounded dark:bg-[hsl(222.2_84%_4.9%)]" max-width="7xl"
            panel-classes="bg-white rounded dark:bg-[hsl(222.2_84%_4.9%)]">
            <div class="flex flex-col h-screen">
                <div class="flex justify-between mb-4">
                    <div class="text-lg sm:text-2xl font-bold tracking-tight">
                        <h1>Comparison Details</h1>
                    </div>
                    <div class="flex items-start mr-8">
                        <component :is="getSimilarityBadge(props.detection.similarity_score)"
                            class="text-2xs sm:text-sm" />
                    </div>
                </div>

                <div class="flex justify-between mb-6 items-start tracking-tight">
                    <div class="flex flex-col text-start">
                        <span class="font-medium">{{ props.detection.submission_a.student_name }}</span>
                        <span class="text-xs text-muted-foreground font-light">{{
                            props.detection.submission_a.student_no
                        }}</span>
                    </div>
                    <div class="flex flex-col text-end">
                        <span class="font-medium">{{ props.detection.submission_b.student_name }}</span>
                        <span class="text-xs text-muted-foreground font-light">{{
                            props.detection.submission_b.student_no
                        }}</span>
                    </div>
                </div>

                <ResizablePanelGroup direction="horizontal" class="bg-neutral-800 border rounded-md tracking-tight">
                    <ResizablePanel :default-size="50">
                        <div class="flex h-full">
                            <ScrollArea class="flex-1 min-h-0">
                                <div class="overflow-x-auto text-xs">
                                    <pre
                                        class="line-numbers p-2"><code :class="`language-${getLanguageFromExtension(props.detection.submission_a.language?.split('.').pop())}`">{{ props.fileA }}</code></pre>
                                </div>
                            </ScrollArea>
                        </div>
                    </ResizablePanel>
                    <ResizableHandle />
                    <ResizablePanel :default-size="50">
                        <div class="flex h-full">
                            <ScrollArea class="flex-1 min-h-0">
                                <div class="overflow-x-auto text-xs">
                                    <pre
                                        class="line-numbers p-2"><code :class="`language-${getLanguageFromExtension(props.detection.submission_b.language?.split('.').pop())}`">{{ props.fileB }}</code></pre>
                                </div>
                            </ScrollArea>
                        </div>
                    </ResizablePanel>
                </ResizablePanelGroup>
            </div>
        </Modal>
    </div>
</template>
