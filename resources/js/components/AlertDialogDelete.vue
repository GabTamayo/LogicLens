<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog/';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { Loader2, Trash2 } from 'lucide-vue-next';
import { defineProps } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    endpoint: string;
    type: string;
    buttonText?: string;
    itemName?: string;
}>();

const form = useForm({});

const handleDelete = () => {
    form.delete(props.endpoint, {
        onSuccess: () => {
            toast(`${props.itemName.charAt(0).toUpperCase() + props.itemName.slice(1)} deleted`, {
                description: `The ${props.type} has been permanently removed.`,
            });
        },
        onError: () => {
            toast.error(`Failed to delete ${props.type}`, {
                description: 'Please try again later.',
            });
        },
    });
};
</script>

<template>
    <AlertDialog as-child>
        <AlertDialogTrigger as-child>
            <Button variant="destructive" class="flex cursor-pointer items-center gap-2">
                <Trash2 class="h-4 w-4" />
                <span class="hidden sm:inline">{{ buttonText ?? 'Delete' }}</span>
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle class="text-destructive">Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    Deleting
                    <span v-if="itemName" class="font-semibold">{{ itemName }}</span>
                    will permanently remove the {{ type }} and all associated data from our servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel :disabled="form.processing">Cancel</AlertDialogCancel>
                <Button variant="destructive" @click="handleDelete" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Delete
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
