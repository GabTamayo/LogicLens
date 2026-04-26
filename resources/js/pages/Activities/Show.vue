<script setup lang="ts">
import AlertDialogDelete from '@/components/AlertDialogDelete.vue';
import DateTimePicker from '@/components/DateTimePicker.vue';
import DateTimePickerDialog from '@/components/DateTimePickerDialog.vue';
import InputError from '@/components/InputError.vue';
import PaginationComponent from '@/components/Pagination.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Drawer, DrawerContent, DrawerDescription, DrawerHeader, DrawerTitle, DrawerTrigger } from '@/components/ui/drawer';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import SelectLabel from '@/components/ui/select/SelectLabel.vue';
import { Separator } from '@/components/ui/separator';
import { Toaster } from '@/components/ui/sonner';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useDeadline } from '@/composables/useDeadline';
import { useLanguage } from '@/composables/useLanguage';
import AppLayout from '@/layouts/AppLayout.vue';
import { ActivityDetail, type BreadcrumbItem } from '@/types';
import { Deferred, Head, Link, router, useForm, usePoll } from '@inertiajs/vue3';
import { createReusableTemplate, useMediaQuery } from '@vueuse/core';
import {
    CalendarCog,
    Circle,
    Clock,
    Code2,
    Copy,
    Delete,
    Eye,
    FileText,
    Link2,
    Loader,
    MoreHorizontal,
    Pencil,
    Plus,
    Save,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

const props = defineProps<ActivityDetail>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.title, href: `/activities/${props.id}` },
];

// Deadline utilities
const { formatRelativeDeadline, formatExpiresAt, getDeadlineStatus } = useDeadline();

// Content Management
const isContentEmpty = computed(() => {
    if (!props.content) return true;
    const trimmed = props.content.trim();
    return trimmed === '' || trimmed === '<p></p>';
});

const isEditing = ref(false);
const editForm = useForm({
    content: props.content,
});

const toggleEdit = () => {
    if (isEditing.value) {
        editForm.content = props.content;
    }
    isEditing.value = !isEditing.value;
};

const saveContent = () => {
    editForm.patch(`/activities/${props.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            toast.success('Content updated', {
                description: 'The activity content has been saved successfully.',
            });
        },
        onError: () => {
            toast.error('Failed to update content', {
                description: editForm.errors.content || 'An error occurred while saving.',
            });
        },
    });
};

// Time Limit Management
const isEditingTimeLimit = ref(false);
const timeLimitForm = useForm({
    time_limit: props.time_limit,
});

const toggleEditTimeLimit = () => {
    if (isEditingTimeLimit.value) {
        timeLimitForm.time_limit = props.time_limit;
    }
    isEditingTimeLimit.value = !isEditingTimeLimit.value;
};

const saveTimeLimit = () => {
    timeLimitForm.patch(`/activities/${props.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditingTimeLimit.value = false;
            toast.success('Time limit updated', {
                description: 'The time limit has been saved successfully.',
            });
        },
        onError: () => {
            toast.error('Failed to update time limit', {
                description: timeLimitForm.errors.time_limit || 'An error occurred while saving.',
            });
        },
    });
};

// Test Cases Management
interface TestCase {
    title: string;
    input: string;
    output: string;
    score: number;
}

const isTestCasesEmpty = computed(() => {
    return !props.test_cases || props.test_cases.length === 0;
});

const isEditingTestCases = ref(false);
const testCasesForm = useForm({
    test_cases: props.test_cases.map((tc) => ({
        title: tc.title,
        input: tc.input,
        output: tc.output,
        score: tc.score,
    })) as TestCase[],
});

const toggleEditTestCases = () => {
    if (isEditingTestCases.value) {
        testCasesForm.test_cases = props.test_cases.map((tc) => ({
            title: tc.title,
            input: tc.input,
            output: tc.output,
            score: tc.score,
        }));
    }
    isEditingTestCases.value = !isEditingTestCases.value;
};

const addTestCase = () => {
    testCasesForm.test_cases.unshift({
        title: '',
        input: '',
        output: '',
        score: 0,
    });
};

const removeTestCase = (index: number) => {
    testCasesForm.test_cases.splice(index, 1);
};

const saveTestCases = () => {
    testCasesForm.patch(`/activities/${props.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditingTestCases.value = false;
            toast.success('Test cases updated', {
                description: 'The test cases have been saved successfully.',
            });
        },
        onError: () => {
            toast.error('Failed to update test cases', {
                description: 'An error occurred while saving test cases.',
            });
        },
    });
};

// Link Generation Form
const form = useForm({
    course_id: '',
    expires_at: null,
});

const submit = () => {
    const formatToServerDateTime = (date: Date): string => {
        return date.toISOString();
    };

    form.transform((data) => ({
        course_id: data.course_id,
        expires_at: data.expires_at ? formatToServerDateTime(data.expires_at as Date) : null,
    })).post(`/activities/${props.id}/links`, {
        onSuccess: () => {
            form.reset('course_id', 'expires_at');
            toast.success('Submission link generated', {
                description: 'You can now use the link for student submissions.',
            });
        },
        onError: () => {
            toast.error('Failed to generate link', {
                description: form.errors.course_id || form.errors.expires_at || 'An error occurred.',
            });
        },
    });
};

// Link Status Management
const updateStatus = (id: number, name: string, value: boolean) => {
    const linkStatusForm = useForm({ is_open: value });
    linkStatusForm.patch(`/activities/${props.id}/links/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Link status updated', {
                description: `${name} is now ${value ? 'open' : 'closed'}.`,
            });
        },
        onError: () => {
            const errorMessage = (linkStatusForm.errors as any).expires_at || 'Failed to update link status. Please try again.';
            toast.error('Cannot update link status', {
                description: errorMessage,
            });
        },
    });
};

// Deadline Management
const removeDeadline = (linkId: number) => {
    router.delete(`/activities/${props.id}/links/${linkId}/deadline`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Deadline removed', {
                description: 'The deadline has been successfully removed from this link.',
            });
        },
        onError: () => {
            toast.error('Failed to remove deadline', {
                description: 'An error occurred while removing the deadline.',
            });
        },
    });
};

// Pagination
const handlePageChange = (page: number) => {
    router.get(
        `/activities/${props.id}`,
        { page },
        {
            preserveScroll: true,
            only: ['links'],
        },
    );
};

function copy(text: string) {
    navigator.clipboard.writeText(text);
    toast('Link copied to clipboard', {
        description: 'The submission link has been copied.',
    });
}

const [UseTemplate, GridForm] = createReusableTemplate();
const isDesktop = useMediaQuery('(min-width: 420px)');
const isOpen = ref(false);
const selectedLinkId = ref<number | null>(null);
const deadlineDate = ref<Date | null>(null);

const openDeadlineDialog = (linkId: number, currentDeadline: string | null) => {
    selectedLinkId.value = linkId;
    deadlineDate.value = currentDeadline ? new Date(currentDeadline) : null;
    isOpen.value = true;
};

const handleSaveDeadline = (payload: { linkId: string; date: Date }) => {
    const deadlineForm = useForm({
        is_open: true,
        expires_at: payload.date.toISOString(),
    });
    deadlineForm.patch(`/activities/${props.id}/links/${payload.linkId}`, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            selectedLinkId.value = null;
            deadlineDate.value = null;
            toast.success('Deadline updated', {
                description: 'The deadline has been set successfully.',
            });
            router.reload({ only: ['links'] });
        },
        onError: () => {
            const errorMessage = (deadlineForm.errors as any).expires_at || 'An error occurred while setting the deadline.';
            toast.error('Failed to set deadline', {
                description: errorMessage,
            });
        },
    });
};

// Loading State
const isInitialLoadDone = ref(false);

// Poll for updates every 30 seconds
usePoll(30000, {
    only: ['links'],
});

const { getLanguageColor, getLanguageLogo } = useLanguage();
</script>

<template>
    <Head :title="`${props.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${props.id}`" type="activity" buttonText="Delete Activity" :itemName="props.title" />
        </template>

        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <div>
                <h1 class="cursor-default text-lg font-bold tracking-tight sm:text-2xl">{{ props.title }}</h1>
            </div>
            <Card>
                <CardHeader>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap items-center gap-3">
                                <CardTitle>Assign Activity to Course</CardTitle>
                                <Badge :class="['px-2.5 py-1 text-xs font-medium', getLanguageColor(props.language_text)]">
                                    <div class="flex items-center gap-1.5">
                                        <img
                                            v-if="getLanguageLogo(props.language_text)"
                                            :src="getLanguageLogo(props.language_text)!"
                                            :alt="`${props.language_text} logo`"
                                            class="h-4 w-4 object-contain"
                                        />
                                        <Code2 v-else class="h-4 w-4" />
                                        <span>{{ props.language_text }}</span>
                                    </div>
                                </Badge>
                            </div>
                            <CardDescription> Collect student submissions for review and analysis. </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Form @submit="submit" class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField name="course_id">
                                <FormItem>
                                    <FormLabel>Course</FormLabel>
                                    <FormControl>
                                        <Select v-model="form.course_id" :disabled="form.processing">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Select a course" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>
                                                        {{ props.courses.length === 0 ? 'No course to be assigned.' : 'Courses' }}
                                                    </SelectLabel>
                                                    <SelectItem v-for="course in props.courses" :key="course.id" :value="course.id">
                                                        {{ course.name }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </FormControl>
                                    <FormDescription class="text-xs"> Select the course for this submission link. </FormDescription>
                                </FormItem>
                            </FormField>

                            <FormField name="expires_at">
                                <FormItem>
                                    <FormLabel>
                                        Deadline
                                        <span class="text-muted-foreground">(Optional)</span>
                                    </FormLabel>
                                    <FormControl>
                                        <DateTimePicker v-model="form.expires_at" :disabled="form.processing" />
                                    </FormControl>
                                    <FormDescription class="text-xs"> Set when submissions should close automatically. </FormDescription>
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing || !form.course_id">
                                <Loader v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                {{ form.processing ? 'Generating...' : 'Generate Link' }}
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>

            <!-- Content Section -->
            <Card class="flex flex-col">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Activity Content</CardTitle>
                            <CardDescription>Instructions and requirements for students</CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <template v-if="isEditing">
                                <Button variant="outline" size="sm" @click="toggleEdit" :disabled="editForm.processing">
                                    <X class="mr-2 h-4 w-4" />
                                    Cancel
                                </Button>
                                <Button size="sm" @click="saveContent" :disabled="editForm.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ editForm.processing ? 'Saving...' : 'Save' }}
                                </Button>
                            </template>
                            <Button v-else-if="!isContentEmpty" variant="outline" size="sm" @click="toggleEdit">
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <ScrollArea class="max-h-[600px]">
                    <CardContent>
                        <div v-if="!isEditing" class="min-h-[200px] border">
                            <div v-if="isContentEmpty" class="flex flex-col items-center justify-center p-12 text-center">
                                <div class="mb-4 rounded-full bg-muted p-3">
                                    <FileText class="h-6 w-6 text-muted-foreground" />
                                </div>
                                <h3 class="mb-1 text-lg font-semibold">No content added yet</h3>
                                <p class="mb-4 max-w-sm text-sm text-muted-foreground">
                                    Add instructions, requirements, or details for this activity
                                </p>
                                <Button variant="outline" size="sm" @click="toggleEdit">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Add Content
                                </Button>
                            </div>

                            <div v-else class="prose max-w-none p-4 dark:prose-invert" v-html="props.content"></div>
                        </div>

                        <div v-else>
                            <RichTextEditor v-model="editForm.content" />
                        </div>
                    </CardContent>
                </ScrollArea>
            </Card>

            <!-- Time Limit Section -->
            <Card class="flex flex-col">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Time Limit</CardTitle>
                            <CardDescription class="text-xs md:text-sm">Set optional time limit for student submissions</CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <template v-if="isEditingTimeLimit">
                                <Button variant="outline" size="sm" @click="toggleEditTimeLimit" :disabled="timeLimitForm.processing">
                                    <X class="mr-2 h-4 w-4" />
                                    Cancel
                                </Button>
                                <Button size="sm" @click="saveTimeLimit" :disabled="timeLimitForm.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ timeLimitForm.processing ? 'Saving...' : 'Save' }}
                                </Button>
                            </template>
                            <Button v-else variant="outline" size="sm" @click="toggleEditTimeLimit">
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="!isEditingTimeLimit" class="flex items-center gap-3">
                        <Clock class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <p v-if="props.time_limit" class="text-sm font-medium">
                                {{ props.time_limit }} minute{{ props.time_limit !== 1 ? 's' : '' }}
                            </p>
                            <p v-else class="text-sm text-muted-foreground">No time limit set</p>
                            <p class="text-xs text-muted-foreground">
                                {{ props.time_limit ? 'Students must complete within this time' : 'Students can take as long as needed' }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Time Limit
                                <span class="font-light text-muted-foreground">(Optional, in minutes)</span>
                            </label>
                            <NumberField
                                class="w-1/2"
                                v-model="timeLimitForm.time_limit"
                                :min="1"
                                :max="1440"
                                :format-options="{
                                    useGrouping: false,
                                }"
                            >
                                <NumberFieldContent>
                                    <NumberFieldDecrement />
                                    <NumberFieldInput placeholder="No time limit" />
                                    <NumberFieldIncrement />
                                </NumberFieldContent>
                            </NumberField>
                            <p class="text-[0.8rem] text-muted-foreground">
                                Set a time limit for this activity (1-1440 minutes). Leave empty for no time limit.
                            </p>
                            <InputError :message="timeLimitForm.errors.time_limit" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Test Cases Section -->
            <Card class="flex flex-col">
                <CardHeader>
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <CardTitle>Test Cases</CardTitle>
                            <CardDescription class="text-xs md:text-sm">Define test cases to automatically grade student submissions</CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <template v-if="isEditingTestCases">
                                <Button variant="outline" size="sm" @click="toggleEditTestCases" :disabled="testCasesForm.processing">
                                    <X class="mr-2 h-4 w-4" />
                                    Cancel
                                </Button>
                                <Button size="sm" @click="saveTestCases" :disabled="testCasesForm.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ testCasesForm.processing ? 'Saving...' : 'Save' }}
                                </Button>
                            </template>
                            <Button v-else-if="!isTestCasesEmpty" variant="outline" size="sm" @click="toggleEditTestCases">
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <ScrollArea class="max-h-[600px]">
                    <CardContent>
                        <div v-if="!isEditingTestCases">
                            <div v-if="isTestCasesEmpty" class="flex min-h-[200px] flex-col items-center justify-center border p-12 text-center">
                                <div class="mb-4 rounded-full bg-muted p-3">
                                    <FileText class="h-6 w-6 text-muted-foreground" />
                                </div>
                                <h3 class="mb-1 text-lg font-semibold">No test cases added yet</h3>
                                <p class="mb-4 max-w-sm text-sm text-muted-foreground">
                                    Add test cases to automatically evaluate student submissions
                                </p>
                                <Button variant="outline" size="sm" @click="toggleEditTestCases">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Add Test Cases
                                </Button>
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="(testCase, index) in props.test_cases" :key="index" class="space-y-3 rounded-lg border bg-muted/10 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <Badge>{{ testCase.score }} pts</Badge>
                                            <h4 class="font-medium">{{ testCase.title || `Test Case ${index + 1}` }}</h4>
                                        </div>
                                    </div>
                                    <div v-if="testCase.input" class="space-y-1">
                                        <p class="text-sm font-medium text-muted-foreground">Input:</p>
                                        <pre class="rounded-md bg-muted p-3 text-sm">{{ testCase.input }}</pre>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium text-muted-foreground">Expected Output:</p>
                                        <pre class="rounded-md bg-muted p-3 text-sm">{{ testCase.output }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ testCasesForm.test_cases.length }} test case{{ testCasesForm.test_cases.length !== 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button class="rounded-full" type="button" variant="outline" @click="addTestCase">
                                                <Plus class="h-4 w-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>Add test case</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>

                            <div v-if="testCasesForm.test_cases.length > 0" class="space-y-4">
                                <div
                                    v-for="(testCase, index) in testCasesForm.test_cases"
                                    :key="index"
                                    class="space-y-3 rounded-lg border bg-muted/10 p-4"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-2">
                                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                                Score
                                                <span class="font-light text-muted-foreground">(Points)</span>
                                            </label>
                                            <NumberField
                                                v-model="testCase.score"
                                                :step="0.5"
                                                :format-options="{
                                                    signDisplay: 'exceptZero',
                                                    minimumFractionDigits: 1,
                                                }"
                                            >
                                                <NumberFieldContent>
                                                    <NumberFieldDecrement />
                                                    <NumberFieldInput />
                                                    <NumberFieldIncrement />
                                                </NumberFieldContent>
                                            </NumberField>
                                            <InputError :message="(testCasesForm.errors as any)[`test_cases.${index}.score`]" />
                                        </div>
                                        <Button type="button" variant="destructive" size="icon" @click="removeTestCase(index)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            Title
                                        </label>
                                        <Input type="text" v-model="testCase.title" placeholder="e.g., Basic Addition" />
                                        <InputError :message="(testCasesForm.errors as any)[`test_cases.${index}.title`]" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            Input
                                            <span class="font-light text-muted-foreground">(Optional)</span>
                                        </label>
                                        <Textarea v-model="testCase.input" placeholder="Input for the test case" :rows="2" />
                                        <InputError :message="(testCasesForm.errors as any)[`test_cases.${index}.input`]" />
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            Expected Output
                                        </label>
                                        <Textarea v-model="testCase.output" placeholder="Expected output for the test case" :rows="2" />
                                        <InputError :message="(testCasesForm.errors as any)[`test_cases.${index}.output`]" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </ScrollArea>
            </Card>

            <Separator />

            <!-- Manage Links Section -->
            <div>
                <div class="mb-4">
                    <h4 class="mb-2 scroll-m-20 text-xl font-semibold tracking-tight">Manage Link Submissions</h4>
                    <p class="text-sm text-muted-foreground">Review existing submission links, update their status, and manage deadlines.</p>
                </div>

                <Deferred data="links" @resolve="isInitialLoadDone = true">
                    <template #fallback>
                        <div v-if="!isInitialLoadDone" class="flex items-center justify-center rounded-md border p-12">
                            <Loader class="mr-2 h-6 w-6 animate-spin text-muted-foreground" />
                            <span class="text-muted-foreground">Loading submission links...</span>
                        </div>
                    </template>

                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[200px]">Course</TableHead>
                                    <TableHead class="w-[180px]">Status</TableHead>
                                    <TableHead class="min-w-[250px]">Link</TableHead>
                                    <TableHead class="w-[100px] text-center">Submissions</TableHead>
                                    <TableHead class="w-[60px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="props.links.data.length > 0">
                                    <TableRow v-for="link in props.links.data" :key="link.id">
                                        <TableCell class="font-medium">
                                            <div class="max-w-[180px] truncate" :title="link.course?.name">
                                                {{ link.course?.name }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col gap-2">
                                                <div class="flex items-center gap-2">
                                                    <Badge variant="outline" class="h-6 w-18">
                                                        <Circle
                                                            class="size-4"
                                                            :class="link.is_open ? 'fill-green-500 text-green-500' : 'fill-red-500 text-red-500'"
                                                        />
                                                        {{ link.is_open ? 'Open' : 'Closed' }}
                                                    </Badge>
                                                    <Switch
                                                        v-model="link.is_open"
                                                        @update:modelValue="updateStatus(link.id, link.course?.name ?? 'Link', $event)"
                                                    />
                                                </div>
                                                <TooltipProvider v-if="link.expires_at">
                                                    <Tooltip>
                                                        <TooltipTrigger as-child>
                                                            <div class="flex cursor-default items-center gap-1.5">
                                                                <component
                                                                    :is="getDeadlineStatus(link.expires_at)?.icon"
                                                                    :class="['h-3.5 w-3.5 shrink-0', getDeadlineStatus(link.expires_at)?.class]"
                                                                />
                                                                <span
                                                                    :class="[
                                                                        'truncate text-xs font-medium',
                                                                        getDeadlineStatus(link.expires_at)?.class,
                                                                    ]"
                                                                >
                                                                    {{ formatRelativeDeadline(link.expires_at) }}
                                                                </span>
                                                            </div>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p class="font-medium">{{ formatExpiresAt(link.expires_at) }}</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                                <div v-else class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                    <Clock class="h-3.5 w-3.5" />
                                                    <span>No deadline</span>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex items-center gap-2">
                                                <code
                                                    class="relative max-w-[200px] truncate rounded bg-muted px-2 py-1 font-mono text-xs md:max-w-full"
                                                >
                                                    {{ props.appUrl }}/student/submit/{{ link.token }}
                                                </code>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="copy(`${props.appUrl}/student/submit/${link.token}`)"
                                                    aria-label="Copy link"
                                                >
                                                    <Copy class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge class="h-[25px] w-[30px] overflow-hidden rounded-full text-ellipsis">
                                                <span class="font-mono font-semibold">
                                                    {{ link.submissions_count }}
                                                </span>
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon" class="h-8 w-8 cursor-pointer p-0" aria-label="Open menu">
                                                        <span class="sr-only">Open menu</span>
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                    <Link :href="`/activities/${props.id}/links/${link.id}`" prefetch="mount">
                                                        <DropdownMenuItem>
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View Submissions
                                                        </DropdownMenuItem>
                                                    </Link>
                                                    <DropdownMenuSeparator />
                                                    <Dialog v-if="isDesktop" v-model:open="isOpen">
                                                        <DialogTrigger as-child>
                                                            <DropdownMenuItem @select.prevent="openDeadlineDialog(link.id, link.expires_at)">
                                                                <CalendarCog class="mr-2 h-4 w-4" />
                                                                Set Deadline
                                                            </DropdownMenuItem>
                                                        </DialogTrigger>
                                                        <DialogContent class="sm:max-w-[425px]">
                                                            <DialogHeader>
                                                                <DialogTitle>Set Deadline</DialogTitle>
                                                                <DialogDescription> Set the date and time for the deadline. </DialogDescription>
                                                            </DialogHeader>
                                                            <GridForm />
                                                        </DialogContent>
                                                    </Dialog>
                                                    <Drawer v-else v-model:open="isOpen">
                                                        <DrawerTrigger as-child>
                                                            <DropdownMenuItem @select.prevent="openDeadlineDialog(link.id, link.expires_at)">
                                                                <CalendarCog class="mr-2 h-4 w-4" />
                                                                Set Deadline
                                                            </DropdownMenuItem>
                                                            <DrawerContent>
                                                                <DrawerHeader>
                                                                    <DrawerTitle>Set Deadline</DrawerTitle>
                                                                    <DrawerDescription> Set the date and time for the deadline. </DrawerDescription>
                                                                </DrawerHeader>
                                                                <GridForm />
                                                            </DrawerContent>
                                                        </DrawerTrigger>
                                                    </Drawer>
                                                    <DropdownMenuItem
                                                        class="text-red-600 dark:text-red-400"
                                                        :disabled="!link.expires_at"
                                                        @click="link.expires_at && removeDeadline(link.id)"
                                                    >
                                                        <Delete class="mr-2 h-4 w-4" />
                                                        Remove Deadline
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow>
                                        <TableCell colspan="5" class="h-32 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <Link2 class="h-8 w-8 text-muted-foreground" />
                                                <p class="text-sm text-muted-foreground">No submission links generated yet.</p>
                                                <p class="text-xs text-muted-foreground">
                                                    Create your first link above to start collecting submissions.
                                                </p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </Deferred>

                <PaginationComponent
                    v-if="props.links && props.links.data.length > 0"
                    class="mt-4"
                    :pagination="props.links"
                    @page-change="handlePageChange"
                />
            </div>
        </div>
    </AppLayout>
    <Toaster rich-colors />

    <UseTemplate>
        <DateTimePickerDialog v-model="deadlineDate" :link-id="selectedLinkId ? selectedLinkId.toString() : ''" @save="handleSaveDeadline" />
    </UseTemplate>
</template>
