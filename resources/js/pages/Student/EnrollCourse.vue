<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from "@/components/ui/form";
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { EnrollCourseProps } from '@/types';
import 'vue-sonner/style.css';

defineProps<EnrollCourseProps>();

const form = useForm({
    access_code: '',
});

function submit(close: () => void) {
    form.post('/student/enroll', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Successfully enrolled in the course!');
            close();
        },
        onError: () => {
            const errorMessage = form.errors.access_code
            toast.error('Failed to enroll. Please try again.', {
                description: errorMessage
            });
        },
    });
}
</script>

<template>
    <Modal max-width="md" position="center" v-slot="{ close }"
        panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <Form class="space-y-6" @submit="submit(close)">
            <FormField name="access_code">
                <FormItem>
                    <div class="mb-4">
                        <div class="flex items-center">
                            <div>
                                <h1 class="font-bold text-lg cursor-default">Enroll in a Course</h1>
                            </div>
                        </div>
                        <FormDescription>
                            Enter the access code provided by your Professor
                        </FormDescription>
                    </div>
                    <FormLabel>Access Code</FormLabel>
                    <FormControl>
                        <Input id="access_code" v-model="form.access_code" type="text"
                            placeholder="Enter course access code" :disabled="form.processing" class="font-mono" />
                    </FormControl>
                </FormItem>
            </FormField>

            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Enrolling...' : 'Enroll' }}
                </Button>
            </div>
        </Form>
    </Modal>
</template>
