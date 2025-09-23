<script setup lang="ts">
import { defineProps } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
} from '@/components/ui/alert-dialog/';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps<{
    endpoint: string;
    title: string;
    type: string;
    buttonText?: string;
}>();

const form = useForm({});

const handleDelete = () => {
    form.delete(props.endpoint);
};


</script>

<template>
    <AlertDialog as-child>
        <AlertDialogTrigger as-child>
            <Button variant="destructive" class="cursor-pointer">{{ buttonText ?? 'Delete' }}</Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle class="text-destructive">Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    Deleting this {{ type }} will permanently remove the {{ type }} and
                    all associated data from our servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel :disabled="form.processing">Cancel</AlertDialogCancel>
                <Button variant="destructive" @click="handleDelete" :disabled="form.processing" class="cursor-pointer">
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Delete
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
