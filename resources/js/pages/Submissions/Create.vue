<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GalleryVerticalEnd, File } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SubmissionPageProps } from '@/types';

defineProps<SubmissionPageProps>()
</script>

<template>

    <Head :title="name" />

    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 bg-cover bg-center"
        :style="{ backgroundImage: `url(${bgImage})` }">
        <div class="w-full max-w-lg">
            <div class="flex flex-col gap-6">

                <form class="bg-card border border-border p-6 shadow-sm rounded-xl">
                    <div class="flex flex-col gap-6">
                        <div class="flex flex-col items-center gap-2">
                            <a href="#" class="flex flex-col items-center gap-2 font-medium">
                                <div class="flex h-8 w-8 items-center justify-center rounded-md">
                                    <GalleryVerticalEnd class="size-6" />
                                </div>
                                <span class="sr-only">Clonewave</span>
                            </a>
                            <h1 class="text-xl font-bold">
                                Welcome to Clonewave
                            </h1>
                            <div class="text-center text-sm text-muted-foreground">
                                <p>Submission for {{ activityName }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-6">
                            <div class="grid gap-2">
                                <Label html-for="name">Name</Label>
                                <Input id="name" type="text" placeholder="John Doe" />
                            </div>
                            <div class="grid gap-2">
                                <Label html-for="stud_no">Student Number</Label>
                                <Input id="stud_no" type="text" placeholder="2#-#####-###" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="picture">Code File</Label>
                                <div class="relative">
                                    <Input id="picture" type="file" class="pl-10" />
                                    <File class="absolute left-2 top-1/2 -translate-y-1/2 size-5 text-gray-500" />
                                </div>
                            </div>

                            <Button type="submit" class="w-full">
                                Submit
                            </Button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>
