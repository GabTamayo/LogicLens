<script setup lang="ts">
import { defineProps } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { AlertDialog, AlertDialogTrigger, AlertDialogContent, AlertDialogHeader, AlertDialogTitle, AlertDialogDescription, AlertDialogFooter, AlertDialogCancel, } from '@/components/ui/alert-dialog/';
import { Loader2, Trash2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner'

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
            <Button variant="destructive" class="cursor-pointer flex items-center gap-2">
                <Trash2 class="w-4 h-4" />
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
