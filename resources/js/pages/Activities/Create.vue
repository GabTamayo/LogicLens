<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import InputError from '@/components/InputError.vue';
import { toast } from 'vue-sonner';

const form = useForm({
    title: '',
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
    <Modal class="bg-white rounded dark:bg-stone-900" max-width="md" position="top" v-slot="{ close }">
        <Form class="space-y-6" @submit="submit(close)">
            <FormField name="title">
                <FormItem>
                    <div class="mb-4">
                        <h1 class="font-bold text-lg">Add Activity</h1>
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

            <Button type="submit" :disabled="form.processing" class="cursor-pointer">
                Save
            </Button>
        </Form>
    </Modal>
</template>
