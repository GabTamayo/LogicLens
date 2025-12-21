<script setup lang="ts">
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { Code, LoaderCircle, CheckCircle2, Play, User, BookOpen, ArrowLeft, Terminal } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { SubmissionPageProps } from '@/types';
import InputError from '@/components/InputError.vue';
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ResizablePanelGroup, ResizablePanel, ResizableHandle } from '@/components/ui/resizable';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import MonacoEditor from '@/components/MonacoEditor.vue';
import { PistonService } from '@/services/piston';
import { Label } from '@/components/ui/label';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

const props = defineProps<SubmissionPageProps>()

const page = usePage();
const submitted = ref(false);
const codeOutput = ref('');
const isRunning = ref(false);
const stdinInput = ref('');
const showStdinInput = ref(false);
const lastSaved = ref<string>('');
const isSavingToBackend = ref(false);

const STORAGE_KEY = `submission_${props.token}`;
let backendSaveTimeout: ReturnType<typeof setTimeout> | null = null;

const form = useForm({
    code_content: '',
});

// Load saved code from backend first, then localStorage as fallback
onMounted(async () => {
    try {
        const response = await fetch(`/student/submission/${props.token}/draft`);
        const data = await response.json();

        if (data.has_draft && data.draft) {
            form.code_content = data.draft.code || '';
            stdinInput.value = data.draft.stdin || '';
            if (data.draft.saved_at) {
                lastSaved.value = new Date(data.draft.saved_at).toLocaleTimeString();
            }
            return;
        }
    } catch (e) {
        console.error('Failed to load draft from backend:', e);
    }

    // Fallback to localStorage
    const savedData = localStorage.getItem(STORAGE_KEY);
    if (savedData) {
        try {
            const parsed = JSON.parse(savedData);
            form.code_content = parsed.code || '';
            stdinInput.value = parsed.stdin || '';
        } catch (e) {
            console.error('Failed to load saved code from localStorage:', e);
        }
    }
});

// Save to backend with debouncing (30 seconds)
const saveToBackend = async () => {
    if (isSavingToBackend.value) {
        return;
    }

    isSavingToBackend.value = true;

    try {
        const response = await fetch(`/student/submission/${props.token}/draft`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                code: form.code_content,
                stdin: stdinInput.value,
            }),
        });

        const data = await response.json();
        if (data.success && data.saved_at) {
            lastSaved.value = new Date(data.saved_at).toLocaleTimeString();
        }
    } catch (e) {
        console.error('Failed to save draft to backend:', e);
    } finally {
        isSavingToBackend.value = false;
    }
};

// Auto-save code to localStorage (instant) and backend (debounced)
watch([() => form.code_content, stdinInput], () => {
    // Instant localStorage save
    const dataToSave = {
        code: form.code_content,
        stdin: stdinInput.value,
        timestamp: Date.now(),
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));

    // Debounced backend save (30 seconds)
    if (backendSaveTimeout) {
        clearTimeout(backendSaveTimeout);
    }

    backendSaveTimeout = setTimeout(() => {
        saveToBackend();
    }, 30000); // 30 seconds
}, { deep: true });

// Clear saved data on successful submission
const submit = () => {
    form.post(`/student/submit/${props.token}`, {
        onSuccess: () => {
            form.reset();
            submitted.value = true;
            localStorage.removeItem(STORAGE_KEY);
        },
        onError: () => {
            const errorMessage = form.errors.code_content || 'Failed to submit your code.';
            toast.error('Submission Failed', {
                description: errorMessage,
            });
        },
    })
}

const runCode = async () => {
    if (!form.code_content || !props.language) {
        codeOutput.value = 'Error: No code to execute or language not specified.';
        return;
    }

    isRunning.value = true;
    codeOutput.value = 'Executing code...\n';

    try {
        const result = await PistonService.executeCode(
            props.language,
            form.code_content,
            stdinInput.value
        );

        codeOutput.value = PistonService.formatOutput(result);
    } catch (error) {
        codeOutput.value = `Execution Error:\n${error instanceof Error ? error.message : 'Unknown error occurred'}`;
    } finally {
        isRunning.value = false;
    }
}

const toggleStdinInput = () => {
    showStdinInput.value = !showStdinInput.value;
}

// Cleanup on unmount - save immediately before leaving
onBeforeUnmount(() => {
    if (backendSaveTimeout) {
        clearTimeout(backendSaveTimeout);
    }
    // Force immediate save before leaving the page
    if (form.code_content) {
        saveToBackend();
    }
});
</script>

<template>

    <Head :title="props.activityName" />

    <div class="flex h-screen flex-col bg-background">
        <!-- Header -->
        <div class="border-b bg-card px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex aspect-square size-10 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                            <AppLogoIcon class="size-10 fill-current text-white dark:text-black" />
                        </div>
                        <Link
                            :href="$page.props.auth.user.is_student ? `/student/courses/${props.courseId}` : `/activities/${props.activityId}`">
                            <Button variant="ghost" size="sm" class="gap-2">
                                <ArrowLeft class="h-4 w-4" />
                                Back
                            </Button>
                        </Link>
                    </div>
                    <div class="border-l pl-4">
                        <h1 class="text-xl font-bold">{{ props.activityName }}</h1>
                        <p class="text-sm text-muted-foreground">{{ props.courseName }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right text-sm">
                        <p class="font-medium">{{ props.studentName }}</p>
                        <p class="text-muted-foreground">{{ props.studentEmail }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="submitted" class="flex flex-1 items-center justify-center bg-background">
            <div class="flex flex-col items-center gap-4 rounded-lg border bg-card p-12 shadow-sm">
                <CheckCircle2 class="h-20 w-20 text-green-500" />
                <h2 class="text-2xl font-bold text-center">Submission Successful!</h2>
                <p class="text-center text-sm text-muted-foreground">
                    You have successfully submitted your activity.
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else class="flex flex-1 overflow-hidden">
            <ResizablePanelGroup direction="horizontal" class="h-full">
                <!-- Left Panel - Activity Instructions -->
                <ResizablePanel :default-size="25" :min-size="20">
                    <div class="flex h-full flex-col">
                        <div class="border-b bg-muted/40 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <BookOpen class="h-4 w-4" />
                                <h2 class="font-semibold">Activity Instructions</h2>
                            </div>
                        </div>
                        <ScrollArea class="flex-1 p-4">
                            <div>
                                <div v-if="props.activityContent">
                                    <div class="prose prose-sm dark:prose-invert max-w-none rounded-md bg-card p-4"
                                        v-html="props.activityContent">
                                    </div>
                                </div>
                                <div v-else class="rounded-md border border-dashed p-8 text-center">
                                    <BookOpen class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                                    <p class="text-sm text-muted-foreground">No instructions provided</p>
                                </div>
                            </div>
                        </ScrollArea>
                    </div>
                </ResizablePanel>

                <ResizableHandle />

                <!-- Right Section - Code Editor, Test Cases, and Output -->
                <ResizablePanel :default-size="75" :min-size="60">
                    <ResizablePanelGroup direction="vertical" class="h-full">
                        <!-- Top Section - Code Editor and Test Cases -->
                        <ResizablePanel :default-size="70" :min-size="50">
                            <ResizablePanelGroup direction="horizontal" class="h-full">
                                <!-- Middle Panel - Code Editor -->
                                <ResizablePanel :default-size="60" :min-size="40">
                                    <div class="flex h-full flex-col">
                                        <div class="border-b bg-muted/40 px-4 py-3">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <Code class="h-4 w-4" />
                                                    <h2 class="font-semibold">Your Code</h2>
                                                    <span class="text-xs text-muted-foreground">({{ props.languageText
                                                        }})</span>
                                                    <span v-if="lastSaved" class="text-xs text-muted-foreground ml-2">
                                                        • Saved {{ lastSaved }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center">
                                                    <Button size="sm" @click="submit"
                                                        :disabled="form.processing">
                                                        <LoaderCircle v-if="form.processing"
                                                            class="mr-2 h-4 w-4 animate-spin" />
                                                        Submit
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex h-full flex-col">
                                            <MonacoEditor v-model="form.code_content"
                                                :language="props.language || 'java'" />
                                        </div>
                                    </div>
                                </ResizablePanel>

                                <ResizableHandle />

                                <!-- Right Panel - Test Cases -->
                                <ResizablePanel :default-size="40" :min-size="30">
                                    <div class="flex h-full flex-col">
                                        <div class="border-b bg-muted/40 px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <CheckCircle2 class="h-4 w-4" />
                                                <h2 class="font-semibold">Test Cases</h2>
                                            </div>
                                        </div>
                                        <ScrollArea class="flex-1 p-4">
                                            <Accordion type="single" collapsible class="w-full">
                                                <AccordionItem value="test-1">
                                                    <AccordionTrigger>
                                                        <div class="flex items-center gap-2">
                                                            <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                                            <span>Test Case 1</span>
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent>
                                                        <div class="space-y-2 text-sm">
                                                            <div>
                                                                <p class="font-medium">Input:</p>
                                                                <code
                                                                    class="block rounded bg-muted p-2 font-mono">Sample input 1</code>
                                                            </div>
                                                            <div>
                                                                <p class="font-medium">Expected Output:</p>
                                                                <code
                                                                    class="block rounded bg-muted p-2 font-mono">Sample output 1</code>
                                                            </div>
                                                        </div>
                                                    </AccordionContent>
                                                </AccordionItem>

                                                <AccordionItem value="test-2">
                                                    <AccordionTrigger>
                                                        <div class="flex items-center gap-2">
                                                            <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                                            <span>Test Case 2</span>
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent>
                                                        <div class="space-y-2 text-sm">
                                                            <div>
                                                                <p class="font-medium">Input:</p>
                                                                <code
                                                                    class="block rounded bg-muted p-2 font-mono">Sample input 2</code>
                                                            </div>
                                                            <div>
                                                                <p class="font-medium">Expected Output:</p>
                                                                <code
                                                                    class="block rounded bg-muted p-2 font-mono">Sample output 2</code>
                                                            </div>
                                                        </div>
                                                    </AccordionContent>
                                                </AccordionItem>

                                                <AccordionItem value="test-3">
                                                    <AccordionTrigger>
                                                        <div class="flex items-center gap-2">
                                                            <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                                            <span>Test Case 3</span>
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent>
                                                        <div class="space-y-2 text-sm">
                                                            <div>
                                                                <p class="font-medium">Input:</p>
                                                                <code
                                                                    class="block rounded bg-muted p-2 font-mono">Sample input 3</code>
                                                            </div>
                                                            <div>
                                                                <p class="font-medium">Expected Output:</p>
                                                                <code
                                                                    class="block rounded bg-muted p-2 font-mono">Sample output 3</code>
                                                            </div>
                                                        </div>
                                                    </AccordionContent>
                                                </AccordionItem>
                                            </Accordion>
                                        </ScrollArea>
                                    </div>
                                </ResizablePanel>
                            </ResizablePanelGroup>
                        </ResizablePanel>

                        <ResizableHandle withHandle />

                        <!-- Bottom Section - Input & Output -->
                        <ResizablePanel :default-size="30" :min-size="20">
                            <div class="flex h-full flex-col border-t">
                                <div class="bg-muted/40 px-4 py-2 flex items-center justify-between gap-2">
                                    <div class="flex items-center">
                                        <Terminal class="h-4 w-4" />
                                        <h3 class="font-semibold text-sm">Console</h3>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Button size="sm" variant="outline" @click="runCode"
                                            :disabled="isRunning">
                                            <LoaderCircle v-if="isRunning" class="mr-2 h-4 w-4 animate-spin" />
                                            <Play v-else class="mr-2 h-4 w-4 fill-current stroke-none" />
                                            Run Code
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="toggleStdinInput" class="h-7 text-xs">
                                            {{ showStdinInput ? 'Hide Input' : 'Show Input' }}
                                        </Button>
                                    </div>
                                </div>

                                <!-- Input Section (collapsible) -->
                                <div v-if="showStdinInput" class="border-b bg-muted/20 p-3">
                                    <Label class="text-xs font-medium mb-1.5 block">
                                        Standard Input (stdin)
                                    </Label>
                                    <Textarea v-model="stdinInput"
                                        placeholder="Enter input for your program (one value per line)&#10;Example:&#10;John&#10;25&#10;New York"
                                        class="min-h-20 resize-none font-mono text-xs" :disabled="isRunning" />
                                    <p class="text-xs text-muted-foreground mt-1.5">
                                        This will be passed to your program via standard input (e.g., Scanner in Java,
                                        input() in Python)
                                    </p>
                                </div>

                                <!-- Output Section -->
                                <ScrollArea class="flex-1 bg-black p-4 font-mono text-sm text-green-400">
                                    <pre v-if="codeOutput" class="whitespace-pre-wrap">{{ codeOutput }}</pre>
                                    <p v-else class="text-muted-foreground">Run your code to see the output here...</p>
                                </ScrollArea>
                            </div>
                        </ResizablePanel>
                    </ResizablePanelGroup>
                </ResizablePanel>
            </ResizablePanelGroup>
        </div>
    </div>
    <Toaster rich-colors />
</template>

<style scoped>
/* Custom styles for code editor appearance */
#code_content {
    tab-size: 4;
    -moz-tab-size: 4;
}

/* Make the textarea fill the container properly */
#code_content {
    min-height: 100%;
}
</style>
