<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import MonacoEditor from '@/components/MonacoEditor.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Kbd } from '@/components/ui/kbd';
import { Label } from '@/components/ui/label';
import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from '@/components/ui/resizable';
import { ScrollArea } from '@/components/ui/scroll-area';
import Separator from '@/components/ui/separator/Separator.vue';
import { Toaster } from '@/components/ui/sonner';
import { Textarea } from '@/components/ui/textarea';
import { PistonService } from '@/services/piston';
import { SubmissionPageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useCountdown } from '@vueuse/core';
import { ArrowLeft, BookOpen, Check, CheckCircle2, Code, LoaderCircle, Play, Terminal } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

const props = defineProps<SubmissionPageProps>();

const submitted = ref(false);
const codeOutput = ref('');
const isRunning = ref(false);
const stdinInput = ref('');
const showStdinInput = ref(false);
const lastSaved = ref<string>('');
const isSavingToBackend = ref(false);
const showSubmitDialog = ref(false);
const testCaseResults = ref<Record<string, { output: string; passed: boolean; isRunning: boolean }>>({});
const initialCountdown = ref((props.timer ?? 0) * 60); // Convert minutes to SECONDS

const { remaining } = useCountdown(initialCountdown, {
    immediate: props.timer ? true : false, // Only start countdown if timer is set
    onComplete() {
        if (props.timer && !submitted.value && !props.hasSubmitted) {
            toast.warning('Time is up!', {
                description: 'Auto-submitting your work...',
            });

            setTimeout(() => {
                submit();
            }, 1000);
        }
    },
});

const formattedTime = computed(() => {
    const minutes = Math.floor(remaining.value / 60);
    const seconds = remaining.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

const windowWidth = ref(window.innerWidth);
const windowHeight = ref(window.innerHeight);

const isMobile = computed(() => windowWidth.value < 768);
const isPortrait = computed(() => windowHeight.value > windowWidth.value);
const isLandscape = computed(() => windowWidth.value > windowHeight.value);

const showInstructions = ref(true);
const showTestCases = ref(false);
const showConsole = ref(false);

const updateViewport = () => {
    const wasPortrait = isPortrait.value;
    windowWidth.value = window.innerWidth;
    windowHeight.value = window.innerHeight;

    if (wasPortrait !== isPortrait.value && isMobile.value) {
        showInstructions.value = true;
        showTestCases.value = false;
        showConsole.value = false;
    }
};

const STORAGE_KEY = `submission_${props.token}`;
let backendSaveTimeout: ReturnType<typeof setTimeout> | null = null;

const form = useForm<{
    code_content: string;
}>({
    code_content: '',
});

const getDefaultCodeTemplate = (): string => {
    if (props.language === 'java') {
        return `public static void main(String[] args) {\n    \n}`;
    }
    return '';
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.shiftKey && event.key === 'F10') {
        event.preventDefault();
        runCode();
    }
};

onMounted(async () => {
    window.addEventListener('resize', updateViewport);
    window.addEventListener('keydown', handleKeyDown);

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

    if (!form.code_content) {
        form.code_content = getDefaultCodeTemplate();
    }
});

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
watch(
    [() => form.code_content, stdinInput],
    () => {
        const dataToSave = {
            code: form.code_content,
            stdin: stdinInput.value,
            timestamp: Date.now(),
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));

        if (backendSaveTimeout) {
            clearTimeout(backendSaveTimeout);
        }

        backendSaveTimeout = setTimeout(() => {
            saveToBackend();
        }, 30000);
    },
    { deep: true },
);

const submit = () => {
    form.post(`/student/submit/${props.token}`, {
        onSuccess: () => {
            form.reset();
            submitted.value = true;
            showSubmitDialog.value = false;
            localStorage.removeItem(STORAGE_KEY);
        },
        onError: () => {
            const errorMessage = form.errors.code_content || 'Failed to submit your code.';
            toast.error('Submission Failed', {
                description: errorMessage,
            });
            showSubmitDialog.value = false;
        },
    });
};

const runCode = async () => {
    if (!form.code_content || !props.language) {
        codeOutput.value = 'Error: No code to execute or language not specified.';
        return;
    }

    isRunning.value = true;
    codeOutput.value = 'Executing code...\n';

    try {
        const result = await PistonService.executeCode(props.language, form.code_content, stdinInput.value);

        codeOutput.value = PistonService.formatOutput(result, stdinInput.value);
    } catch (error) {
        codeOutput.value = `Execution Error:\n${error instanceof Error ? error.message : 'Unknown error occurred'}`;
    } finally {
        isRunning.value = false;
    }
};

const toggleStdinInput = () => {
    showStdinInput.value = !showStdinInput.value;
};

const runTestCase = async (testCaseId: string, input: string, expectedOutput: string) => {
    if (!form.code_content || !props.language) {
        return;
    }

    testCaseResults.value[testCaseId] = {
        output: '',
        passed: false,
        isRunning: true,
    };

    try {
        const result = await PistonService.executeCode(props.language, form.code_content, input);

        // For test cases, use stdout only (without prompts and formatting)
        let actualOutput = result.run.stdout.trim();

        // If there's a compilation error, show it
        if (result.compile && result.compile.code !== 0) {
            actualOutput = result.compile.stderr || result.compile.output || 'Compilation failed';
        }
        // If there's a runtime error, show it
        else if (result.run.stderr) {
            actualOutput = result.run.stderr.trim();
        }

        const expected = expectedOutput.trim();
        const passed = actualOutput === expected;

        testCaseResults.value[testCaseId] = {
            output: actualOutput || '(no output)',
            passed,
            isRunning: false,
        };
    } catch (error) {
        testCaseResults.value[testCaseId] = {
            output: `Execution Error: ${error instanceof Error ? error.message : 'Unknown error occurred'}`,
            passed: false,
            isRunning: false,
        };
    }
};

const runAllTestCases = async () => {
    if (!props.testCases || props.testCases.length === 0) {
        return;
    }

    for (const testCase of props.testCases) {
        await runTestCase(testCase.id, testCase.input, testCase.output);
    }
};

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateViewport);
    window.removeEventListener('keydown', handleKeyDown);

    if (backendSaveTimeout) {
        clearTimeout(backendSaveTimeout);
    }
    if (form.code_content) {
        saveToBackend();
    }
});

const toggleSection = (section: 'instructions' | 'testcases' | 'console') => {
    if (section === 'instructions') {
        showInstructions.value = !showInstructions.value;
        showTestCases.value = false;
        showConsole.value = false;
    } else if (section === 'testcases') {
        showTestCases.value = !showTestCases.value;
        showInstructions.value = false;
        showConsole.value = false;
    } else if (section === 'console') {
        showConsole.value = !showConsole.value;
        showInstructions.value = false;
        showTestCases.value = false;
    }
};
</script>

<template>
    <Head :title="props.activityName" />

    <div class="flex h-screen flex-col bg-background">
        <!-- Header - Desktop -->
        <div v-if="!isMobile" class="border-b bg-card px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex aspect-square size-12 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
                        >
                            <AppLogoIcon class="size-12 fill-current text-white dark:text-black" />
                        </div>
                        <Link :href="$page.props.auth.user.is_student ? `/student/courses/${props.courseId}` : `/activities/${props.activityId}`">
                            <Button variant="ghost" size="sm" class="gap-2">
                                <ArrowLeft class="h-4 w-4" />
                                Back to the Activities
                            </Button>
                        </Link>
                    </div>
                    <Separator orientation="vertical" class="h-14" />
                    <div>
                        <h1 class="text-xl font-bold">{{ props.activityName }}</h1>
                        <p class="text-sm text-muted-foreground">{{ props.courseName }}</p>
                    </div>
                </div>

                <div class="inline-flex items-center gap-18">
                    <div v-if="props.hasSubmitted && !submitted" class="bg-muted/20">
                        <Alert class="flex items-start gap-4">
                            <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-600">
                                <Check class="h-3.5 w-3.5 text-white" />
                            </div>

                            <div>
                                <AlertTitle>Already Submitted</AlertTitle>
                                <AlertDescription> You have already submitted for this activity. </AlertDescription>
                            </div>
                        </Alert>
                    </div>
                    <div v-else-if="props.timer" class="text-sm font-semibold">
                        Time Remaining:
                        <span class="ml-1 rounded bg-destructive/25 p-1 font-mono text-lg text-destructive"> {{ formattedTime }} </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right text-sm">
                            <p class="font-medium">{{ props.studentName }}</p>
                            <p class="text-muted-foreground">{{ props.studentEmail }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header - Mobile -->
        <div v-else class="border-b bg-card px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Link :href="$page.props.auth.user.is_student ? `/student/courses/${props.courseId}` : `/activities/${props.activityId}`">
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-sm font-bold">{{ props.activityName }}</h1>
                        <p class="truncate text-xs text-muted-foreground">{{ props.courseName }}</p>
                    </div>
                </div>
                <div v-if="props.hasSubmitted && !submitted" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-600">
                    <Check class="h-3.5 w-3.5 text-white" />
                </div>

                <div v-else-if="props.timer" class="text-xs font-semibold">
                    Time Remaining:
                    <span class="ml-1 rounded bg-destructive/25 p-1 font-mono text-md text-destructive"> {{ formattedTime }} </span>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="submitted" class="flex flex-1 items-center justify-center bg-background">
            <div class="flex flex-col items-center gap-4 rounded-lg border bg-card p-12 shadow-sm">
                <CheckCircle2 class="h-20 w-20 text-green-500" />
                <h2 class="text-center text-2xl font-bold">Submission Successful!</h2>
                <p class="text-center text-sm text-muted-foreground">You have successfully submitted your activity.</p>
            </div>
        </div>

        <!-- Main Content - Desktop & Mobile Landscape -->
        <div v-else-if="!isMobile || isLandscape" class="flex flex-1 overflow-hidden">
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
                        <ScrollArea class="m-2 flex-1 rounded-lg border p-4">
                            <div>
                                <div v-if="props.activityContent">
                                    <div
                                        class="prose prose-sm max-w-none rounded-md bg-card p-4 dark:prose-invert"
                                        v-html="props.activityContent"
                                    ></div>
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
                                                    <span class="text-xs text-muted-foreground">({{ props.languageText }})</span>
                                                    <span v-if="lastSaved" class="ml-2 text-xs text-muted-foreground"> • Saved {{ lastSaved }} </span>
                                                </div>
                                                <div class="flex items-center">
                                                    <Dialog v-model:open="showSubmitDialog">
                                                        <DialogTrigger as-child>
                                                            <Button size="sm" :disabled="form.processing"> Submit </Button>
                                                        </DialogTrigger>
                                                        <DialogContent class="sm:max-w-md">
                                                            <DialogHeader>
                                                                <DialogTitle>Ready to submit?</DialogTitle>
                                                                <DialogDescription>
                                                                    Review your work carefully. You won't be able to make any changes once submitted.
                                                                </DialogDescription>
                                                            </DialogHeader>
                                                            <DialogFooter>
                                                                <DialogClose as-child>
                                                                    <Button variant="outline"> Cancel </Button>
                                                                </DialogClose>
                                                                <Button @click="submit" :disabled="form.processing">
                                                                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                                                    Confirm Submit
                                                                </Button>
                                                            </DialogFooter>
                                                        </DialogContent>
                                                    </Dialog>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex h-full flex-col">
                                            <MonacoEditor v-model="form.code_content" :language="props.language || 'java'" />
                                        </div>
                                    </div>
                                </ResizablePanel>

                                <ResizableHandle />

                                <!-- Right Panel - Test Cases -->
                                <ResizablePanel :default-size="40" :min-size="30">
                                    <div class="flex h-full flex-col">
                                        <div class="border-b bg-muted/40 px-4 py-3">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <CheckCircle2 class="h-4 w-4" />
                                                    <h2 class="font-semibold">Test Cases</h2>
                                                </div>
                                                <Button
                                                    v-if="props.testCases && props.testCases.length > 0"
                                                    size="sm"
                                                    variant="outline"
                                                    @click="runAllTestCases"
                                                    :disabled="!form.code_content || isRunning"
                                                    class="gap-2"
                                                >
                                                    <Play class="h-3 w-3 fill-current stroke-none" />
                                                    Run All Tests
                                                </Button>
                                            </div>
                                        </div>
                                        <ScrollArea class="flex-1 p-4">
                                            <div v-if="props.testCases && props.testCases.length > 0">
                                                <Accordion type="single" collapsible class="w-full">
                                                    <AccordionItem
                                                        v-for="(testCase, index) in props.testCases"
                                                        :key="testCase.id"
                                                        :value="`test-${testCase.id}`"
                                                    >
                                                        <AccordionTrigger>
                                                            <div class="flex items-center gap-2">
                                                                <LoaderCircle
                                                                    v-if="testCaseResults[testCase.id]?.isRunning"
                                                                    class="h-3 w-3 animate-spin text-blue-500"
                                                                />
                                                                <div
                                                                    v-else
                                                                    class="h-2 w-2 rounded-full"
                                                                    :class="{
                                                                        'bg-green-500': testCaseResults[testCase.id]?.passed,
                                                                        'bg-red-500':
                                                                            testCaseResults[testCase.id] &&
                                                                            !testCaseResults[testCase.id]?.passed &&
                                                                            !testCaseResults[testCase.id]?.isRunning,
                                                                        'bg-yellow-500': !testCaseResults[testCase.id],
                                                                    }"
                                                                ></div>
                                                                <span>{{ testCase.title || `Test Case ${index + 1}` }}</span>
                                                            </div>
                                                        </AccordionTrigger>
                                                        <AccordionContent>
                                                            <div class="space-y-3 text-sm">
                                                                <div>
                                                                    <p class="font-medium">Input:</p>
                                                                    <code class="block rounded bg-muted p-2 font-mono whitespace-pre-wrap">{{
                                                                        testCase.input
                                                                    }}</code>
                                                                </div>
                                                                <div>
                                                                    <p class="font-medium">Expected Output:</p>
                                                                    <code class="block rounded bg-muted p-2 font-mono whitespace-pre-wrap">{{
                                                                        testCase.output
                                                                    }}</code>
                                                                </div>
                                                                <div v-if="testCaseResults[testCase.id] && !testCaseResults[testCase.id]?.isRunning">
                                                                    <div class="flex items-center justify-between">
                                                                        <p class="font-medium">Actual Output:</p>
                                                                        <span
                                                                            class="text-xs font-semibold"
                                                                            :class="{
                                                                                'text-green-600 dark:text-green-500':
                                                                                    testCaseResults[testCase.id]?.passed,
                                                                                'text-red-600 dark:text-red-500':
                                                                                    !testCaseResults[testCase.id]?.passed,
                                                                            }"
                                                                        >
                                                                            {{ testCaseResults[testCase.id]?.passed ? '✓ PASSED' : '✗ FAILED' }}
                                                                        </span>
                                                                    </div>
                                                                    <code
                                                                        class="block rounded p-2 font-mono whitespace-pre-wrap"
                                                                        :class="{
                                                                            'border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20':
                                                                                testCaseResults[testCase.id]?.passed,
                                                                            'border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/20':
                                                                                !testCaseResults[testCase.id]?.passed,
                                                                        }"
                                                                        >{{ testCaseResults[testCase.id]?.output || '(no output)' }}</code
                                                                    >
                                                                </div>
                                                                <Button
                                                                    size="sm"
                                                                    variant="secondary"
                                                                    @click="runTestCase(testCase.id, testCase.input, testCase.output)"
                                                                    :disabled="!form.code_content || testCaseResults[testCase.id]?.isRunning"
                                                                    class="w-full gap-2"
                                                                >
                                                                    <LoaderCircle
                                                                        v-if="testCaseResults[testCase.id]?.isRunning"
                                                                        class="h-4 w-4 animate-spin"
                                                                    />
                                                                    <Play v-else class="h-4 w-4 fill-current stroke-none" />
                                                                    {{ testCaseResults[testCase.id]?.isRunning ? 'Running...' : 'Run This Test' }}
                                                                </Button>
                                                            </div>
                                                        </AccordionContent>
                                                    </AccordionItem>
                                                </Accordion>
                                            </div>
                                            <div v-else class="rounded-md border border-dashed p-8 text-center">
                                                <CheckCircle2 class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                                                <p class="text-sm text-muted-foreground">No test cases available</p>
                                            </div>
                                        </ScrollArea>
                                    </div>
                                </ResizablePanel>
                            </ResizablePanelGroup>
                        </ResizablePanel>

                        <ResizableHandle withHandle />

                        <!-- Bottom Section - Input & Output -->
                        <ResizablePanel :default-size="30" :min-size="20">
                            <div class="flex h-full flex-col border-t">
                                <div class="flex items-center justify-between gap-2 bg-muted/40 px-4 py-2">
                                    <div class="flex items-center">
                                        <Terminal class="h-4 w-4" />
                                        <h3 class="text-sm font-semibold">Console</h3>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Button size="sm" variant="outline" @click="runCode" :disabled="isRunning" class="gap-2">
                                            <LoaderCircle v-if="isRunning" class="h-4 w-4 animate-spin" />
                                            <Play v-else class="h-4 w-4 fill-current stroke-none" />
                                            <span>Run Code</span>
                                            <Kbd class="hidden lg:inline-flex">Shift+F10</Kbd>
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="toggleStdinInput" class="text-sm">
                                            {{ showStdinInput ? 'Hide Input' : 'Show Input' }}
                                        </Button>
                                    </div>
                                </div>

                                <!-- Input Section (collapsible) -->
                                <div v-if="showStdinInput" class="border-b bg-muted/20 p-3">
                                    <Label class="mb-1.5 block text-xs font-medium"> Standard Input (stdin) </Label>
                                    <Textarea
                                        v-model="stdinInput"
                                        placeholder="Enter input for your program (one value per line)&#10;Example:&#10;John&#10;25&#10;New York"
                                        class="min-h-20 resize-none font-mono text-xs"
                                        :disabled="isRunning"
                                    />
                                    <p class="mt-1.5 text-xs text-muted-foreground">
                                        This will be passed to your program via standard input (e.g., Scanner in Java, input() in Python)
                                    </p>
                                </div>

                                <!-- Output Section -->
                                <ScrollArea class="flex-1 bg-stone-900 p-4 font-mono text-sm text-white">
                                    <pre v-if="codeOutput" class="whitespace-pre-wrap">{{ codeOutput }}</pre>
                                    <p v-else class="text-muted-foreground">Run your code to see the output here...</p>
                                </ScrollArea>
                            </div>
                        </ResizablePanel>
                    </ResizablePanelGroup>
                </ResizablePanel>
            </ResizablePanelGroup>
        </div>

        <!-- Main Content - Mobile Portrait -->
        <div v-else class="flex flex-1 flex-col overflow-hidden">
            <!-- Code Editor (Always visible on mobile portrait - Top) -->
            <div class="flex flex-col border-b" :class="showInstructions || showTestCases || showConsole ? 'h-64' : 'flex-1'">
                <div class="border-b bg-muted/40 px-4 py-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Code class="h-4 w-4" />
                            <h2 class="text-sm font-semibold">Your Code</h2>
                            <span class="text-xs text-muted-foreground">({{ props.languageText }})</span>
                        </div>
                        <Dialog v-model:open="showSubmitDialog">
                            <DialogTrigger as-child>
                                <Button size="sm" :disabled="form.processing"> Submit </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-md">
                                <DialogHeader>
                                    <DialogTitle>Submit Your Code</DialogTitle>
                                    <DialogDescription>
                                        Are you sure you want to submit your code? Make sure you've tested it thoroughly.
                                    </DialogDescription>
                                </DialogHeader>
                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button variant="outline"> Cancel </Button>
                                    </DialogClose>
                                    <Button @click="submit" :disabled="form.processing">
                                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        Confirm Submit
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
                <div class="flex-1">
                    <MonacoEditor v-model="form.code_content" :language="props.language || 'java'" />
                </div>
            </div>

            <!-- Mobile Navigation Tabs -->
            <div class="flex border-b bg-muted/40">
                <button
                    @click="toggleSection('instructions')"
                    :class="[
                        'flex-1 px-4 py-3 text-sm font-medium transition-colors',
                        showInstructions ? 'border-b-2 border-primary bg-background text-foreground' : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <div class="flex items-center justify-center gap-2">
                        <BookOpen class="h-4 w-4" />
                        <span>Instructions</span>
                    </div>
                </button>
                <button
                    @click="toggleSection('testcases')"
                    :class="[
                        'flex-1 px-4 py-3 text-sm font-medium transition-colors',
                        showTestCases ? 'border-b-2 border-primary bg-background text-foreground' : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <div class="flex items-center justify-center gap-2">
                        <CheckCircle2 class="h-4 w-4" />
                        <span>Tests</span>
                    </div>
                </button>
                <button
                    @click="toggleSection('console')"
                    :class="[
                        'flex-1 px-4 py-3 text-sm font-medium transition-colors',
                        showConsole ? 'border-b-2 border-primary bg-background text-foreground' : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <div class="flex items-center justify-center gap-2">
                        <Terminal class="h-4 w-4" />
                        <span>Console</span>
                    </div>
                </button>
            </div>

            <!-- Mobile Content Sections -->
            <div class="flex flex-1 flex-col overflow-hidden" v-if="showInstructions || showTestCases || showConsole">
                <!-- Instructions Section (Mobile) -->
                <div v-if="showInstructions" class="flex flex-1 flex-col overflow-hidden">
                    <ScrollArea class="flex-1 p-4">
                        <div v-if="props.activityContent">
                            <div class="prose prose-sm max-w-none rounded-md bg-card p-4 dark:prose-invert" v-html="props.activityContent"></div>
                        </div>
                        <div v-else class="rounded-md border border-dashed p-8 text-center">
                            <BookOpen class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                            <p class="text-sm text-muted-foreground">No instructions provided</p>
                        </div>
                    </ScrollArea>
                </div>

                <!-- Test Cases Section (Mobile) -->
                <div v-else-if="showTestCases" class="flex flex-1 flex-col overflow-hidden">
                    <div v-if="props.testCases && props.testCases.length > 0" class="border-b bg-muted/40 px-4 py-2">
                        <Button size="sm" variant="outline" @click="runAllTestCases" :disabled="!form.code_content || isRunning" class="w-full gap-2">
                            <Play class="h-3 w-3 fill-current stroke-none" />
                            Run All Tests
                        </Button>
                    </div>
                    <ScrollArea class="flex-1 p-4">
                        <div v-if="props.testCases && props.testCases.length > 0">
                            <Accordion type="single" collapsible class="w-full">
                                <AccordionItem v-for="(testCase, index) in props.testCases" :key="testCase.id" :value="`test-${testCase.id}`">
                                    <AccordionTrigger>
                                        <div class="flex items-center gap-2">
                                            <LoaderCircle v-if="testCaseResults[testCase.id]?.isRunning" class="h-3 w-3 animate-spin text-blue-500" />
                                            <div
                                                v-else
                                                class="h-2 w-2 rounded-full"
                                                :class="{
                                                    'bg-green-500': testCaseResults[testCase.id]?.passed,
                                                    'bg-red-500':
                                                        testCaseResults[testCase.id] &&
                                                        !testCaseResults[testCase.id]?.passed &&
                                                        !testCaseResults[testCase.id]?.isRunning,
                                                    'bg-yellow-500': !testCaseResults[testCase.id],
                                                }"
                                            ></div>
                                            <span>{{ testCase.title || `Test Case ${index + 1}` }}</span>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent>
                                        <div class="space-y-3 text-sm">
                                            <div>
                                                <p class="font-medium">Input:</p>
                                                <code class="block rounded bg-muted p-2 font-mono whitespace-pre-wrap">{{ testCase.input }}</code>
                                            </div>
                                            <div>
                                                <p class="font-medium">Expected Output:</p>
                                                <code class="block rounded bg-muted p-2 font-mono whitespace-pre-wrap">{{ testCase.output }}</code>
                                            </div>
                                            <div v-if="testCaseResults[testCase.id] && !testCaseResults[testCase.id]?.isRunning">
                                                <div class="flex items-center justify-between">
                                                    <p class="font-medium">Actual Output:</p>
                                                    <span
                                                        class="text-xs font-semibold"
                                                        :class="{
                                                            'text-green-600 dark:text-green-500': testCaseResults[testCase.id]?.passed,
                                                            'text-red-600 dark:text-red-500': !testCaseResults[testCase.id]?.passed,
                                                        }"
                                                    >
                                                        {{ testCaseResults[testCase.id]?.passed ? '✓ PASSED' : '✗ FAILED' }}
                                                    </span>
                                                </div>
                                                <code
                                                    class="block rounded p-2 font-mono whitespace-pre-wrap"
                                                    :class="{
                                                        'border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20':
                                                            testCaseResults[testCase.id]?.passed,
                                                        'border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/20':
                                                            !testCaseResults[testCase.id]?.passed,
                                                    }"
                                                    >{{ testCaseResults[testCase.id]?.output || '(no output)' }}</code
                                                >
                                            </div>
                                            <Button
                                                size="sm"
                                                variant="secondary"
                                                @click="runTestCase(testCase.id, testCase.input, testCase.output)"
                                                :disabled="!form.code_content || testCaseResults[testCase.id]?.isRunning"
                                                class="w-full gap-2"
                                            >
                                                <LoaderCircle v-if="testCaseResults[testCase.id]?.isRunning" class="h-4 w-4 animate-spin" />
                                                <Play v-else class="h-4 w-4 fill-current stroke-none" />
                                                {{ testCaseResults[testCase.id]?.isRunning ? 'Running...' : 'Run This Test' }}
                                            </Button>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                        <div v-else class="rounded-md border border-dashed p-8 text-center">
                            <CheckCircle2 class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                            <p class="text-sm text-muted-foreground">No test cases available</p>
                        </div>
                    </ScrollArea>
                </div>

                <!-- Console Section (Mobile) -->
                <div v-else-if="showConsole" class="flex flex-1 flex-col overflow-hidden border-t">
                    <div class="flex items-center justify-between gap-2 bg-muted/40 px-4 py-2">
                        <div class="flex items-center gap-2">
                            <Terminal class="h-4 w-4" />
                            <h3 class="text-sm font-semibold">Console Output</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button size="sm" variant="outline" @click="runCode" :disabled="isRunning">
                                <LoaderCircle v-if="isRunning" class="mr-2 h-4 w-4 animate-spin" />
                                <Play v-else class="mr-2 h-4 w-4 fill-current stroke-none" />
                                Run
                            </Button>
                            <Button size="sm" variant="ghost" @click="toggleStdinInput" class="h-7 text-xs">
                                {{ showStdinInput ? 'Hide Input' : 'Input' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Input Section (collapsible) -->
                    <div v-if="showStdinInput" class="border-b bg-muted/20 p-3">
                        <Label class="mb-1.5 block text-xs font-medium"> Standard Input (stdin) </Label>
                        <Textarea
                            v-model="stdinInput"
                            placeholder="Enter input for your program..."
                            class="min-h-20 resize-none font-mono text-xs"
                            :disabled="isRunning"
                        />
                    </div>

                    <!-- Output Section -->
                    <ScrollArea class="flex-1 bg-black p-4 font-mono text-sm text-green-400">
                        <pre v-if="codeOutput" class="whitespace-pre-wrap">{{ codeOutput }}</pre>
                        <p v-else class="text-muted-foreground">Run your code to see the output here...</p>
                    </ScrollArea>
                </div>
            </div>
        </div>
    </div>
    <Toaster rich-colors />
</template>

<style scoped>
#code_content {
    tab-size: 4;
    -moz-tab-size: 4;
}

#code_content {
    min-height: 100%;
}
</style>
