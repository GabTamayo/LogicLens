<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge';
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger, } from '@/components/ui/alert-dialog/';
import { Loader2 } from 'lucide-vue-next';


interface Props {
    id: number;
    title: string;
    links: Array<{
        id: number;
        name: string;
        token: string;
        status: string;
        expires_at?: string | null;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.title, href: `/activities/${props.id}` },
];

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(`/activities/${props.id}/links`, {
        onSuccess: () => {
            form.reset('name');
        }
    })
}

const handleDelete = () => {
    form.delete(`/activities/${props.id}`, {
    })
}

</script>

<template>

    <Head title="Activity Name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialog as-child>
                <AlertDialogTrigger as-child>
                    <Button variant="destructive" class="cursor-pointer">
                        Delete Activity
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Deleting this activity will permanently remove the activity, all its submission tokens, and
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

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Generate Token Submission</h4>
                <p class="text-sm text-muted-foreground">
                    A submission token allows you to store student submissions for later detection.
                </p>
                <Form @submit="submit" class="space-y-6 flex items-center justify-center space-x-12 m-6">
                    <Button type="submit" :disabled="form.processing">Generate</Button>
                    <FormField name="name">
                        <FormItem class="w-full">
                            <FormLabel>Token Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.name" />
                            </FormControl>
                            <FormDescription>
                                Enter your desired submission token name.
                            </FormDescription>
                            <InputError :message="form.errors.name" />
                        </FormItem>
                    </FormField>
                </Form>
            </div>

            <Separator />

            <div>
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Manage Token Submission</h4>
                <p class="text-sm text-muted-foreground">
                    You may need a cross detection or delete any of your existing submission tokens.
                </p>
                <Table class="mt-4">
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[200px]">Name</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-center">Token</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="link in links" :key="link.id">
                            <TableCell>{{ link.name }}</TableCell>
                            <TableCell>
                                <Badge variant="secondary">{{ link.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-center w-0 font-mono">{{ link.token }}</TableCell>
                            <TableCell class="text-right">
                                <a href="#" class="text-gray-600 hover:underline text-sm">View Details</a>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
