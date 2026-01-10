<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormField, FormItem, FormLabel } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useForm } from '@inertiajs/vue3';
import { Modal } from '@inertiaui/modal-vue';
import { Plus, Trash2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

defineProps<{ languages: Record<string, string> }>();

interface TestCase {
    title: string;
    input: string;
    output: string;
    score: number;
}

const form = useForm({
    title: '',
    language: '',
    content: '',
    time_limit: null as number | null,
    test_cases: [] as TestCase[],
});

function addTestCase() {
    form.test_cases.unshift({
        title: '',
        input: '',
        output: '',
        score: 0,
    });
}

function removeTestCase(index: number) {
    form.test_cases.splice(index, 1);
}

function submit(close: () => void) {
    form.post('/activities', {
        onSuccess: () => {
            toast.success('Activity created successfully!');
            close();
        },
        onError: () => {
            toast.error('Failed to create activity. Please try again.');
        },
    });
}
</script>

<template>
    <Modal max-width="7xl" position="top" v-slot="{ close }" panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <div class="flex h-[85vh] flex-col">
            <!-- Fixed Header -->
            <div class="mb-6">
                <h1 class="cursor-default text-lg font-bold">Add Activity</h1>
                <p class="text-[0.8rem] text-muted-foreground">Add an activity to generate submission links. Click Save once you're done.</p>
            </div>

            <!-- Scrollable Content -->
            <ScrollArea class="flex-1 pr-4">
                <Form class="space-y-6" @submit="submit(close)">
                    <FormField name="title">
                        <FormItem>
                            <FormLabel>Title</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.title" />
                            </FormControl>
                            <InputError :message="form.errors.title" />
                        </FormItem>
                    </FormField>
                    <FormField name="language">
                        <FormItem>
                            <FormLabel>Programming Language</FormLabel>
                            <FormControl>
                                <Select v-model="form.language">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select Programming Language" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Programming Language</SelectLabel>
                                            <SelectItem v-for="(label, value) in languages" :key="value" :value="value">
                                                {{ label }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <InputError :message="form.errors.language" />
                        </FormItem>
                    </FormField>

                    <FormField name="time_limit">
                        <FormItem>
                            <FormLabel>
                                Time Limit
                                <span class="font-light text-muted-foreground">(Optional, in minutes)</span>
                            </FormLabel>
                            <FormControl class="w-1/2">
                                <NumberField
                                    v-model="form.time_limit"
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
                            </FormControl>
                            <p class="text-[0.8rem] text-muted-foreground">
                                Set a time limit for this activity (1-1440 minutes). Leave empty for no time limit.
                            </p>
                            <InputError :message="form.errors.time_limit" />
                        </FormItem>
                    </FormField>

                    <FormField name="content">
                        <FormItem>
                            <FormLabel>
                                Content
                                <span class="font-light text-muted-foreground">(Optional)</span>
                            </FormLabel>
                            <FormControl>
                                <div class="overflow-hidden">
                                    <RichTextEditor v-model="form.content" />
                                </div>
                            </FormControl>
                            <InputError :message="form.errors.content" />
                        </FormItem>
                    </FormField>

                    <!-- Test Cases Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Test Cases
                                    <span class="font-light text-muted-foreground">(Optional)</span>
                                    <span class="ml-4 text-xs font-light text-muted-foreground"> {{ form.test_cases.length }} test cases</span>
                                </label>
                                <p class="mt-1 text-[0.8rem] text-muted-foreground">Add test cases to grade student submissions</p>
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

                        <div v-if="form.test_cases.length > 0" class="space-y-4">
                            <div v-for="(testCase, index) in form.test_cases" :key="index" class="space-y-3 rounded-lg border bg-muted/10 p-4">
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
                                        <InputError :message="(form.errors as any)[`test_cases.${index}.score`]" />
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
                                    <InputError :message="(form.errors as any)[`test_cases.${index}.title`]" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        Input
                                        <span class="font-light text-muted-foreground">(Optional)</span>
                                    </label>
                                    <Textarea v-model="testCase.input" placeholder="Input for the test case" :rows="2" />
                                    <InputError :message="(form.errors as any)[`test_cases.${index}.input`]" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        Expected Output
                                    </label>
                                    <Textarea v-model="testCase.output" placeholder="Expected output for the test case" :rows="2" />
                                    <InputError :message="(form.errors as any)[`test_cases.${index}.output`]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pb-4">
                        <!-- Spacer for bottom padding -->
                    </div>
                </Form>
            </ScrollArea>

            <!-- Fixed Footer with Submit Button -->
            <div class="mt-4 flex flex-shrink-0 justify-end border-t pt-6">
                <Button type="submit" :disabled="form.processing" @click="submit(close)">
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </Button>
            </div>
        </div>
    </Modal>
</template>
