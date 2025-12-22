<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
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
    test_cases: [] as TestCase[],
});

function addTestCase() {
    form.test_cases.push({
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
        <Form class="space-y-6" @submit="submit(close)">
            <FormField name="title">
                <FormItem>
                    <div class="mb-4">
                        <h1 class="cursor-default text-lg font-bold">Add Activity</h1>
                        <FormDescription> Add an activity to generate submission links. Click Save once you're done. </FormDescription>
                    </div>
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
                        </label>
                        <p class="mt-1 text-[0.8rem] text-muted-foreground">Add test cases to automatically grade student submissions</p>
                    </div>
                    <Button type="button" variant="outline" size="sm" @click="addTestCase">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Test Case
                    </Button>
                </div>

                <div v-if="form.test_cases.length > 0" class="space-y-4">
                    <div v-for="(testCase, index) in form.test_cases" :key="index" class="space-y-3 rounded-lg border bg-muted/10 p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium">Test Case {{ index + 1 }}</h4>
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
                            <label class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"> Title </label>
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

            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </Button>
            </div>
        </Form>
    </Modal>
</template>
