<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, ActivityDetail } from '@/types';
import PaginationComponent from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from '@/components/ui/form'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge';
import { Head, useForm, Link, router, WhenVisible } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Switch } from "@/components/ui/switch"
import InputError from '@/components/InputError.vue';
import { Circle, Copy } from 'lucide-vue-next';
import AlertDialogDelete from '@/components/AlertDialogDelete.vue';
import { Skeleton } from "@/components/ui/skeleton"
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';

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
            toast.success('Submission link generated', {
                description: 'You can now use the link for student submissions.',
            });
        },
        onError: () => {
            toast.error('Failed to generate link. Please try again.');
        },
    })
}
const statusForm = useForm({ is_open: false });
const updateStatus = async (id: number, name: string, value: boolean) => {
    statusForm.is_open = value
    try {
        await statusForm.patch(`/activities/${activity.id}/links/${id}`, {
            preserveState: true,
        })
        toast.info('Link status updated', {
            description: `The submission link for ${name} is now ${value ? 'open' : 'closed'}.`,
        })
    } catch (error) {
        toast.error('Failed to update link status', {
            description: 'Please try again later.',
        })
    }
}
const handlePageChange = (page: number) => {
    router.get(`/activities/${activity.id}`, { page }, { preserveScroll: true })
}

function copy(id: string) {
    navigator.clipboard.writeText(id)
    toast('Link copied to clipboard')
}
</script>

<template>

    <Head :title="`${activity.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${activity.id}`" type="activity" buttonText="Delete Activity"
                :itemName="activity.title" />
        </template>

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Generate Link Submission</h4>
                <p class="text-sm text-muted-foreground">
                    A submission link allows you to store student submissions for later detection.
                </p>
                <Form @submit="submit" class="space-y-6 flex items-center justify-center space-x-12 m-6">
                    <Button type="submit" :disabled="form.processing">Generate</Button>
                    <FormField name="name">
                        <FormItem class="w-full">
                            <FormLabel>Link Submission Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.name" />
                            </FormControl>
                            <FormDescription>
                                Enter your desired submission link submission name.
                            </FormDescription>
                            <InputError :message="form.errors.name" />
                        </FormItem>
                    </FormField>
                </Form>
            </div>

            <Separator />

            <div>
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Manage Link Submission</h4>
                <p class="text-sm text-muted-foreground">
                    You may need a cross detection or delete any of your existing submission links.
                </p>
                <WhenVisible data="activity.links.data">
                    <template #fallback>
                        <div class="mt-8 space-y-1">
                            <Skeleton v-for="i in 8" :key="i" class="h-15 w-full rounded-xl" />
                        </div>
                    </template>
                    <Table class="mt-4">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[200px]">Name</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-center">Link</TableHead>
                                <TableHead></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="activity.links.data.length > 0">
                                <TableRow v-for="link in activity.links.data" :key="link.id">
                                    <TableCell>{{ link.name }}</TableCell>
                                    <TableCell>
                                        <div class="flex">
                                            <Badge variant="outline" class="w-18">
                                                <Circle class="size-4" :class="link.is_open
                                                    ? 'fill-green-500 text-green-500'
                                                    : 'fill-red-500 text-red-500'" />
                                                {{ link.is_open ? 'Open' : 'Closed' }}
                                            </Badge>
                                            <Switch class="ml-4" v-model="link.is_open" :disabled="form.processing"
                                                @update:modelValue="updateStatus(link.id, link.name, $event)" />
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="text-center font-mono max-w-xs overflow-hidden whitespace-nowrap truncate">
                                        {{
                                            activity.appUrl }}/submit{{ link.token }}
                                        <Button variant="outline" size="icon"
                                            @click="copy(`${activity.appUrl}/submit${link.token}`)">
                                            <Copy class="w-2 h-2" />
                                        </Button>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Link :href="`/activities/${activity.id}/links/${link.id}`"
                                            class="text-gray-600 hover:underline text-sm">View Submissions</Link>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <template v-else>
                                <TableCell colspan="4" class="text-center text-muted-foreground py-6">
                                    No submission links generated yet.
                                </TableCell>
                            </template>
                        </TableBody>
                    </Table>
                </WhenVisible>
            </div>
            <PaginationComponent :pagination="activity.links" @page-change="handlePageChange" />
        </div>
    </AppLayout>
    <Toaster rich-colors />
</template>
