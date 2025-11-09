<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { GalleryVerticalEnd, File, LoaderCircle, CheckCircle2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SubmissionPageProps } from '@/types';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

const props = defineProps<SubmissionPageProps>()

const submitted = ref(false);

const form = useForm({
    student_name: '',
    student_email: '',
    student_no: '',
    code_file: null as File | null,
});

const submit = () => {
    form.post(`/submit/${props.token}`, {
        onSuccess: () => {
            form.reset();
            submitted.value = true;
        }
    })
}

</script>

<template>

    <Head :title="name" />

    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 bg-cover bg-center"
        :style="{ backgroundImage: `url(${bgImage})` }">
        <div class="w-full max-w-lg">
            <div class="flex flex-col gap-6">
                <div class="bg-card border border-border p-6 shadow-sm rounded-xl">
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex flex-col items-center gap-2 font-medium">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md">
                                <GalleryVerticalEnd class="size-6" />
                            </div>
                            <span class="sr-only">Clonewave</span>
                        </div>
                        <h1 class="text-xl font-bold">
                            Welcome to Clonewave
                        </h1>
                        <div class="text-center text-sm text-muted-foreground">
                            <p>Submission for {{ activityName }}</p>
                        </div>
                    </div>

                    <div v-if="submitted" class="flex flex-col items-center gap-4 bg-card p-10">
                        <CheckCircle2 class="w-20 h-20 text-green-500" />
                        <h2 class="text-2xl font-bold text-center">Submission Successful!</h2>
                        <p class="text-muted-foreground text-center text-sm">You have successfuly submitted your
                            activity.</p>
                    </div>

                    <form v-else @submit.prevent="submit">
                        <div class="flex flex-col gap-6">
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.student_name" type="text" placeholder="Enter Name" />
                                <InputError :message="form.errors.student_name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.student_email" type="text" placeholder="Enter Email" />
                                <InputError :message="form.errors.student_email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="stud_no">Student Number</Label>
                                <Input id="stud_no" v-model="form.student_no" type="text"
                                    placeholder="Enter Student Number" />
                                <InputError :message="form.errors.student_no" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="code_file">Code File</Label>
                                <div class="relative">
                                    <Input id="code_file" type="file"
                                        :accept="allowedExtensions.map(ext => `.${ext}`).join(',')" @change="(e: Event) => {
                                            const target = e.target as HTMLInputElement
                                            form.code_file = target.files?.[0] ?? null
                                        }" class="pl-10" />
                                    <File class="absolute left-2 top-1/2 -translate-y-1/2 size-5 text-gray-500" />
                                </div>
                                <InputError :message="form.errors.code_file" />
                            </div>

                            <Button type="submit" class="w-full" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                Submit
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
