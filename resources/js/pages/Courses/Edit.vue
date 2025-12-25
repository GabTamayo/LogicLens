<script setup lang="ts">
import CoverPhotoPicker from '@/components/CoverPhotoPicker.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import Separator from '@/components/ui/separator/Separator.vue';
import { useForm } from '@inertiajs/vue3';
import { Modal } from '@inertiaui/modal-vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    course: {
        id: string;
        name: string;
        access_code: string;
        cover_photo: string;
    };
    coverPhotos: Array<{ name: string; path: string }>;
}>();

const form = useForm({
    name: props.course.name,
    access_code: props.course.access_code,
    cover_photo: props.course.cover_photo,
});

function submit(close: () => void) {
    form.put(`/courses/${props.course.id}`, {
        onSuccess: () => {
            toast.success('Course updated successfully!');
            close();
        },
        onError: () => {
            toast.error('Failed to update course. Please try again.');
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
    <Modal max-width="2xl" position="top" v-slot="{ close }" panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <Form class="space-y-6 p-6" @submit="submit(close)">
            <div>
                <h1 class="text-2xl font-semibold">Edit Course</h1>
            </div>

            <FormField name="name">
                <FormItem>
                    <FormLabel class="text-lg font-medium">Change course name</FormLabel>
                    <FormControl>
                        <Input type="text" v-model="form.name" placeholder="Section : Course (e.g., 1BSIT-1 : DSA)" />
                    </FormControl>
                    <InputError :message="form.errors.name" />
                </FormItem>
            </FormField>

            <FormField name="access_code">
                <FormItem>
                    <FormLabel class="text-lg font-medium"
                        >Regenerate access code
                        <span class="text-muted-foreground">(6-12 characters)</span>
                    </FormLabel>
                    <FormDescription>
                        Current code: <span class="font-mono font-semibold">{{ props.course.access_code }}</span>
                    </FormDescription>
                    <div class="flex gap-2">
                        <FormControl class="flex-1">
                            <Input type="text" v-model="form.access_code" placeholder="Enter or generate new code" class="font-mono" />
                        </FormControl>
                        <Button type="button" variant="outline" @click="generateAccessCode"> Generate </Button>
                    </div>
                    <InputError :message="form.errors.access_code" />
                </FormItem>
            </FormField>

            <Separator />

            <div class="space-y-4">
                <h2 class="text-lg font-medium">Pick a cover photo</h2>
                <CoverPhotoPicker :cover-photos="props.coverPhotos" v-model="form.cover_photo" />
            </div>

            <div class="flex justify-end gap-2">
                <Button type="button" variant="outline" @click="close"> Cancel </Button>
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </Form>
    </Modal>
</template>
