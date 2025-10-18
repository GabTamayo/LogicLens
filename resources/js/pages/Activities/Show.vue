<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, ActivityDetail } from '@/types';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Switch } from "@/components/ui/switch"
import InputError from '@/components/InputError.vue';
import { Circle, Ellipsis } from 'lucide-vue-next';
import AlertDialogDelete from '@/components/AlertDialogDelete.vue';

const activity = defineProps<ActivityDetail>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: activity.title, href: `/activities/${activity.id}` },
];

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(`/activities/${activity.id}/links`, {
        onSuccess: () => {
            form.reset('name');
        }
    })
}

const updateStatus = (id: number, value: boolean) => {
    useForm({
        is_open: value,
    }).patch(`/activities/${activity.id}/links/${id}`, {
        preserveState: true,
    })
}

</script>

<template>

    <Head :title="`${activity.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${activity.id}`" title="Are you absolutely sure?" type="activity"
                buttonText="Delete Activity" />
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
                        <template v-if="activity.links.length > 0">
                            <TableRow v-for="link in activity.links" :key="link.id">
                                <TableCell>{{ link.name }}</TableCell>
                                <TableCell>
                                    <Badge class="w-18">
                                        <Circle class="size-4" :class="link.is_open
                                            ? 'fill-green-500 text-green-500'
                                            : 'fill-red-500 text-red-500'" />
                                        {{ link.is_open ? 'Open' : 'Closed' }}
                                    </Badge>
                                    <Switch class="ml-4" v-model="link.is_open" :disabled="form.processing"
                                        @update:modelValue="updateStatus(link.id, $event)" />
                                </TableCell>
                                <TableCell
                                    class="text-center font-mono max-w-xs overflow-hidden whitespace-nowrap truncate">{{
                                        activity.appUrl }}/submit{{ link.token }}
                                </TableCell>
                                <TableCell class="text-right w-0">
                                    <Link :href="`/activities/${activity.id}/links/${link.id}`"
                                        class="text-gray-600 hover:underline text-sm">View Submissions</Link>
                                </TableCell>
                            </TableRow>
                        </template>

                        <template v-else>
                            <TableCell colspan="4" class="text-center text-muted-foreground py-6">
                                No submission tokens generated yet.
                            </TableCell>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
