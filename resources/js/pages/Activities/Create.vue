<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage, } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { LoaderCircle } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

const form = useForm({
    title: '',
});

function submit(close: () => void) {
    form.post('/activities', {
        onSuccess: () => close(),
    });
}

</script>

<template>
    <Modal max-width="md" position="top" v-slot="{ close }">
        <form class="space-y-6" @submit.prevent="submit(close)">
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

            <Button type="submit" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                Save
            </Button>

        </form>
    </Modal>
</template>
