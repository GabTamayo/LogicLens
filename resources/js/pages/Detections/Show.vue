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

const props = defineProps<DetectionShowProps>()

const getLanguageFromExtension = (extension?: string): string => {
    if (!extension) return 'plaintext'
    const languageMap: Record<string, string> = { txt: 'java' }
    return languageMap[extension.toLowerCase()] || 'plaintext'
}
const getSimilarityBadge = (score: number) => {
    if (score >= 0.90) {
        return h(Badge, { variant: 'destructive' }, () => 'Very High')
    }
    if (score >= 0.85) {
        return h(Badge, { variant: 'customYellow' }, () => 'High')
    }
    if (score >= 0.80) {
        return h(Badge, { variant: 'secondary' }, () => 'Medium')
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
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold">Comparison Details</h1>
                    <Separator orientation="vertical" class="mx-4 h-6" />
                    <p class="font-semibold">{{ Math.round(props.detection.similarity_score * 100) }}%</p>
                </div>
                <div class="mr-8">
                    <component :is="getSimilarityBadge(props.detection.similarity_score)" />
                </div>
            </div>

            <div class="flex justify-between mb-6 items-start">
                <div class="flex flex-col">
                    <span class="font-medium">{{ props.detection.submission_a.student_name }}</span>
                    <span class="text-xs text-muted-foreground">{{ props.detection.submission_a.student_no }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-medium">{{ props.detection.submission_b.student_name }}</span>
                    <span class="text-xs text-muted-foreground">{{ props.detection.submission_b.student_no }}</span>
                </div>
            </div>

            <ResizablePanelGroup direction="horizontal" class="h-170 border rounded-md">
                <ResizablePanel :default-size="50">
                    <div>
                        <div class="overflow-auto text-xs h-170">
                            <pre
                                class="line-numbers p-4"><code :class="`language-${getLanguageFromExtension(props.detection.submission_a.file_path?.split('.').pop())}`">{{ props.fileA }}</code></pre>
                        </div>
                    </div>
                </ResizablePanel>
                <ResizableHandle />
                <ResizablePanel :default-size="50">
                    <div>
                        <div class="overflow-auto text-xs h-170">
                            <pre
                                class="line-numbers p-4"><code :class="`language-${getLanguageFromExtension(props.detection.submission_b.file_path?.split('.').pop())}`">{{ props.fileB }}</code></pre>
                        </div>
                    </div>
                </ResizablePanel>
            </ResizablePanelGroup>
        </Modal>
    </div>
</template>
