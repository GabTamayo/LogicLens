<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import InputError from '@/components/InputError.vue';
import { toast } from 'vue-sonner';

const form = useForm({
    name: '',
    access_code: '',
    is_active: true,
});

function submit(close: () => void) {
    form.post('/sections', {
        onSuccess: () => {
            toast.success('Section created successfully!');
            close();
        },
        onError: () => {
            toast.error('Failed to create section. Please try again.');
        },
    });
}

function generateAccessCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.access_code = code;
}
</script>

<template>
    <Modal max-width="2xl" position="top" v-slot="{ close }"
        panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <Form class="space-y-6" @submit="submit(close)">
            <FormField name="name">
                <FormItem>
                    <div class="mb-4">
                        <h1 class="font-bold text-lg cursor-default">Create New Section</h1>
                        <FormDescription>
                            Create a new class section with a unique access code for your students.
                        </FormDescription>
                    </div>
                    <FormLabel>Section Name</FormLabel>
                    <FormControl>
                        <Input type="text" v-model="form.name" placeholder="Enter your class (e.g., 1BSIT-1)" />
                    </FormControl>
                    <InputError :message="form.errors.name" />
                </FormItem>
            </FormField>

            <FormField name="access_code">
                <FormItem>
                    <FormLabel>Access Code
                        <span class="text-muted-foreground">(6-12 characters)</span>
                    </FormLabel>
                    <div class="flex gap-2">
                        <FormControl class="flex-1">
                            <Input type="text" v-model="form.access_code"
                                placeholder="Enter or generate code" class="font-mono" />
                        </FormControl>
                        <Button type="button" variant="outline" @click="generateAccessCode">
                            Generate
                        </Button>
                    </div>
                    <InputError :message="form.errors.access_code" />
                </FormItem>
            </FormField>

            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create Section' }}
                </Button>
            </div>
        </Form>
    </Modal>
</template>
