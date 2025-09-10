<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge';
import { Copy } from 'lucide-vue-next';

const page = usePage();
const activity = page.props;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: activity.title, href: `/activities/${activity.id}` },
];

interface Props {
    id: number;
    title: string;
    links: Array<object>;
}

defineProps<Props>();
</script>

<template>

    <Head title="Activity Name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Generate Token Submission</h4>
                <p class="text-sm text-muted-foreground">
                    A submission token allows you to store student submissions for later detection.
                </p>
                <form class="space-y-6 flex items-center justify-center space-x-12 m-6">
                    <Button>
                        Generate
                    </Button>
                    <FormField v-slot="{ componentField }" name="username">
                        <FormItem v-auto-animate class="w-full">
                            <FormLabel>Token Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-bind="componentField" class="px-2 py-2 rounded-md border-2" />
                            </FormControl>
                            <FormDescription>
                                Enter your desired submission token name.
                            </FormDescription>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </form>
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
                            <TableCell class="text-center w-0">{{ link.token }}</TableCell>
                            <TableCell><Copy class="w-4 text-gray-400" /></TableCell>
                            <TableCell class="text-right">View Details</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
