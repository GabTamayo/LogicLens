<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ActivityDetail, type BreadcrumbItem } from '@/types';
import PaginationComponent from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from '@/components/ui/form'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger, } from '@/components/ui/dropdown-menu'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger, } from "@/components/ui/dialog"
import { Drawer, DrawerContent, DrawerDescription, DrawerHeader, DrawerTitle, DrawerTrigger, } from "@/components/ui/drawer"
import { createReusableTemplate, useMediaQuery } from "@vueuse/core"
import { ref } from "vue"
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge';
import { Head, useForm, Link, router, Deferred, usePoll } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Switch } from "@/components/ui/switch"
import { Circle, Copy, MoreHorizontal, Eye, Delete, CalendarCog, Code2 } from 'lucide-vue-next';
import AlertDialogDelete from '@/components/AlertDialogDelete.vue';
import { Skeleton } from "@/components/ui/skeleton"
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import DateTimePicker from '@/components/DateTimePicker.vue';
import { formatDistanceToNow, parseISO } from 'date-fns'
import DateTimePickerDialog from '@/components/DateTimePickerDialog.vue';

const props = defineProps<ActivityDetail>()
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: '/activities' },
    { title: props.title, href: `/activities/${props.id}` },
];
const form = useForm({
    name: '',
    expires_at: null,
});
const submit = () => {
    const formatToServerDateTime = (date: Date): string => {
        return date.toISOString(); // '2025-11-12T12:00:00.000Z'
    };

    form.transform((data) => ({
        name: data.name,
        expires_at: data.expires_at ? formatToServerDateTime(data.expires_at as Date) : null
    })).post(`/activities/${props.id}/links`, {
        onSuccess: () => {
            form.reset('name', 'expires_at');
            toast.success('Submission link generated', {
                description: 'You can now use the link for student submissions.',
            });
        },
        onError: () => {
            toast.error('Failed to generate link.', {
                description: form.errors.name || form.errors.expires_at,
            });
        },
    })
}
const updateStatus = (id: number, name: string, value: boolean) => {
    const linkStatusForm = useForm({ is_open: value });
    linkStatusForm.patch(`/activities/${props.id}/links/${id}`, {
        onError: () => {
            const errorMessage = (linkStatusForm.errors as any).expires_at || 'Failed to update link status. Please try again.';
            toast.error('Cannot update link status', {
                description: errorMessage,
            })
        }
    })
}
const removeDeadline = (linkId: number) => {
    router.delete(`/activities/${props.id}/links/${linkId}/deadline`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Deadline removed', {
                description: 'The deadline has been successfully removed from this link.',
            });
        },
        onError: () => {
            toast.error('Failed to remove deadline', {
                description: 'An error occurred while removing the deadline.',
            });
        },
    });
}
const handlePageChange = (page: number) => {
    router.get(`/activities/${props.id}`,
        { page },
        {
            preserveScroll: true,
            only: ['links']
        }
    )
}
function formatRelativeDeadline(expires_at: string | null) {
    if (!expires_at) return 'No deadline'
    const date = parseISO(expires_at)

    const distance = formatDistanceToNow(date, { addSuffix: true })
    return distance
}
function formatExpiresAt(expires_at: string | null) {
    if (!expires_at) return '';
    const date = new Date(expires_at);
    return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(date);
}
function copy(id: string) {
    navigator.clipboard.writeText(id)
    toast('Link copied to clipboard')
}
const [UseTemplate, GridForm] = createReusableTemplate()
const isDesktop = useMediaQuery("(min-width: 768px)")
const isOpen = ref(false)
const selectedLinkId = ref<number | null>(null)
const deadlineDate = ref<Date | null>(null)
const openDeadlineDialog = (linkId: number, currentDeadline: string | null) => {
    selectedLinkId.value = linkId
    deadlineDate.value = currentDeadline ? new Date(currentDeadline) : null
    isOpen.value = true
}
const handleSaveDeadline = (payload: { linkId: string, date: Date }) => {
    const deadlineForm = useForm({
        is_open: true,
        expires_at: payload.date.toISOString()
    })
    deadlineForm.patch(`/activities/${props.id}/links/${payload.linkId}`, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false
            selectedLinkId.value = null
            deadlineDate.value = null
            toast.info('Deadline updated', {
                description: 'The deadline has been set.',
            })
            router.reload({ only: ['links'] })
        },
        onError: () => {
            const errorMessage = (deadlineForm.errors as any).expires_at || 'An error occurred while setting the deadline.';
            toast.error('Failed to set deadline', {
                description: errorMessage,
            })
        }
    })
}
const isInitialLoadDone = ref(false)

usePoll(30000, {
    only: ['links'],
})

const getLanguageColor = (language: string) => {
    switch (language) {
        case 'Java':
            return 'bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 border-red-200 dark:border-red-800';
        case 'Python':
            return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800';
        default:
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border-blue-200 dark:border-blue-800';
    }
};

const getLanguageLogo = (language: string) => {
    switch (language) {
        case 'Java':
            return '/images/java-logo-png.png';
        case 'Python':
            return '/images/python-logo-png.png';
        default:
            return null;
    }
};
</script>

<template>

    <Head :title="`${props.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <AlertDialogDelete :endpoint="`/activities/${props.id}`" type="activity" buttonText="Delete Activity"
                :itemName="props.title" />
        </template>

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h4 class="cursor-default scroll-m-20 text-xl font-semibold tracking-tight">Generate Link Submission</h4>
                    <div :class="[
                        'px-2.5 py-1 rounded-md border text-xs font-medium',
                        getLanguageColor(props.language_text)
                    ]">
                        <div class="flex items-center gap-1.5">
                            <img v-if="getLanguageLogo(props.language_text)"
                                :src="getLanguageLogo(props.language_text)!"
                                :alt="`${props.language_text} logo`"
                                class="h-5 w-5 object-contain" />
                            <Code2 v-else class="h-5 w-5" />
                            <span>{{ props.language_text }}</span>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    A submission link allows you to store student submissions for later detection.
                </p>
                <Form @submit="submit"
                    class="space-y-6 flex flex-col lg:flex-row lg:items-center lg:justify-center lg:space-x-12 m-6">
                    <Button type="submit" :disabled="form.processing" class="hidden lg:block">Generate</Button>
                    <FormField name="activity">
                        <FormItem class="w-full">
                            <FormLabel>Link Submission Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.name" :message="form.errors.name" />
                            </FormControl>
                            <FormDescription>
                                Enter your desired link submission name.
                            </FormDescription>
                        </FormItem>
                        <FormField name="expires_at">
                            <FormItem class="w-full">
                                <FormLabel>Deadline (Optional)</FormLabel>
                                <FormControl>
                                    <DateTimePicker v-model="form.expires_at" />
                                </FormControl>
                                <FormDescription>
                                    Select the date and time for the deadline.
                                </FormDescription>
                            </FormItem>
                        </FormField>
                    </FormField>
                    <Button type="submit" :disabled="form.processing" class="block lg:hidden">Generate</Button>
                </Form>
            </div>

            <Separator />

            <div>
                <h4 class="scroll-m-20 text-xl font-semibold tracking-tight">Manage Link Submission</h4>
                <Deferred data="links" @resolve="isInitialLoadDone = true">
                    <template #fallback>
                        <div v-if="!isInitialLoadDone" class="mt-8 space-y-1">
                            <Skeleton v-for="i in 8" :key="i" class="h-15 w-full rounded-xl" />
                        </div>
                    </template>
                    <div class="mt-4 rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Link</TableHead>
                                    <TableHead>Submission</TableHead>
                                    <TableHead></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="props.links.data.length > 0">
                                    <TableRow v-for="link in props.links.data" :key="link.id">
                                        <TableCell class="truncate">{{ link.name }}</TableCell>
                                        <TableCell>
                                            <div class="flex">
                                                <Badge variant="outline" class="h-6 w-18">
                                                    <Circle class="size-4" :class="link.is_open
                                                        ? 'fill-green-500 text-green-500'
                                                        : 'fill-red-500 text-red-500'" />
                                                    {{ link.is_open ? 'Open' : 'Closed' }}
                                                </Badge>
                                                <Switch class="ml-4" v-model="link.is_open"
                                                    @update:modelValue="updateStatus(link.id, link.name, $event)" />
                                                <span class="ms-2 text-2xs lg:text-xs text-muted-foreground font-light">
                                                    <div class="hidden 2xl:block">
                                                        {{ formatExpiresAt(link.expires_at) }} ({{
                                                            formatRelativeDeadline(link.expires_at) }})
                                                    </div>
                                                    <div class="block 2xl:hidden truncate">
                                                        {{ formatExpiresAt(link.expires_at) }}
                                                    </div>
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div
                                                class="flex items-center space-x-2 font-mono max-w-xs md:max-w-full truncate">
                                                <span class="truncate">{{ props.appUrl }}/submit{{ link.token }}</span>
                                                <Button variant="ghost" size="icon"
                                                    @click="copy(`${props.appUrl}/submit${link.token}`)">
                                                    <Copy class="w-2 h-2" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge class="rounded-full w-[30px] h-[25px] overflow-hidden text-ellipsis">
                                                <span class="font-mono font-semibold">
                                                    {{ link.submissions_count }}
                                                </span>
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon"
                                                        class="w-8 h-8 p-0 cursor-pointer">
                                                        <span class="sr-only">Open menu</span>
                                                        <MoreHorizontal class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                    <Link :href="`/activities/${props.id}/links/${link.id}`" prefetch='mount'>
                                                    <DropdownMenuItem>
                                                        <Eye class="w-4 h-4 mr-1" />
                                                        View Submissions
                                                    </DropdownMenuItem>
                                                    </Link>
                                                    <DropdownMenuSeparator />
                                                    <Dialog v-if="isDesktop" v-model:open="isOpen">
                                                        <DialogTrigger as-child>
                                                            <DropdownMenuItem
                                                                @select.prevent="openDeadlineDialog(link.id, link.expires_at)">
                                                                <CalendarCog class="w-4 h-4 mr-2" />
                                                                Set Deadline
                                                            </DropdownMenuItem>
                                                        </DialogTrigger>
                                                        <DialogContent class="sm:max-w-[425px]">
                                                            <DialogHeader>
                                                                <DialogTitle>Set Deadline</DialogTitle>
                                                                <DialogDescription>
                                                                    Set the date and time for the deadline.
                                                                </DialogDescription>
                                                            </DialogHeader>
                                                            <GridForm />
                                                        </DialogContent>
                                                    </Dialog>
                                                    <Drawer v-else v-model:open="isOpen">
                                                        <DrawerTrigger as-child>
                                                            <DropdownMenuItem
                                                                @select.prevent="openDeadlineDialog(link.id, link.expires_at)">
                                                                <CalendarCog class="w-4 h-4 mr-2" />
                                                                Set Deadline
                                                            </DropdownMenuItem>
                                                            <DrawerContent>
                                                                <DrawerHeader>
                                                                    <DrawerTitle>Set Deadline</DrawerTitle>
                                                                    <DrawerDescription>
                                                                        Set the date and time for the deadline.
                                                                    </DrawerDescription>
                                                                </DrawerHeader>
                                                                <GridForm />
                                                            </DrawerContent>
                                                        </DrawerTrigger>
                                                    </Drawer>
                                                    <DropdownMenuItem class="text-red-600" :disabled="!link.expires_at"
                                                        @click="link.expires_at && removeDeadline(link.id)">
                                                        <Delete class="w-4 h-4 mr-2" />
                                                        Remove Deadline
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow>
                                        <TableCell colspan="4" class="text-center text-muted-foreground py-6">
                                            No submission links generated yet.
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </Deferred>
            </div>
            <PaginationComponent v-if="props.links" :pagination="props.links" @page-change="handlePageChange" />
        </div>
    </AppLayout>
    <Toaster rich-colors />

    <UseTemplate>
        <DateTimePickerDialog v-model="deadlineDate" :link-id="selectedLinkId ? selectedLinkId.toString() : ''" @save="handleSaveDeadline" />
    </UseTemplate>
</template>
