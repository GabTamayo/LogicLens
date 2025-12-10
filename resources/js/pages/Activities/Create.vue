<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import InputError from '@/components/InputError.vue';
import { toast } from 'vue-sonner';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select';
import RichTextEditor from '@/components/RichTextEditor.vue';

defineProps<{ languages: Record<string, string> }>();
const form = useForm({
    title: '',
    language: '',
    content: '',
});

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
    <Modal max-width="7xl" position="top" v-slot="{ close }"
        panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <Form class="space-y-6" @submit="submit(close)">
            <FormField name="title">
                <FormItem>
                    <div class="mb-4">
                        <h1 class="font-bold text-lg cursor-default">Add Activity</h1>
                        <FormDescription>
                            Add an activity to generate submission links. Click Save once you're done.
                        </FormDescription>
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

            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">
                    Save
                </Button>
            </div>
        </Form>
    </Modal>
</template>
