<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Code, LoaderCircle, CheckCircle2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { SubmissionPageProps } from '@/types';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const props = defineProps<SubmissionPageProps>()

const submitted = ref(false);

const form = useForm({
    code_content: '',
});

const submit = () => {
    form.post(`/student/submit/${props.token}`, {
        onSuccess: () => {
            form.reset();
            submitted.value = true;
        }
    })
}
</script>

<template>

    <Head :title="props.activityName" />

    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 bg-cover bg-center"
        :style="{ backgroundImage: `url(${props.bgImage})` }">
        <div class="w-full max-w-4xl">
            <div class="flex flex-col gap-6">
                <div class="bg-card border border-border p-6 shadow-sm rounded-xl">
                    <div class="flex flex-col items-center gap-2 mb-6">
                        <div class="flex flex-col items-center gap-2 font-medium">
                            <div
                                class="flex aspect-square size-10 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                                <AppLogoIcon class="size-10 fill-current text-white dark:text-black" />
                            </div>
                            <span class="sr-only">LogicLens</span>
                        </div>
                        <h1 class="text-xl font-bold">
                            {{ props.activityName }}
                        </h1>
                        <div class="text-sm text-muted-foreground">
                            <p>{{ props.courseName }}</p>
                            <p>Language: {{ props.languageText }}</p>
                        </div>
                    </div>

                    <div v-if="props.activityContent" class="mb-6 p-4 bg-muted rounded-lg">
                        <h2 class="text-lg font-semibold mb-2">Instructions</h2>
                        <div v-html="props.activityContent" class="prose dark:prose-invert max-w-none"></div>
                    </div>

                    <div class="mb-6 p-4 bg-muted rounded-lg">
                        <p class="text-sm"><strong>Name:</strong> {{ props.studentName }}</p>
                        <p class="text-sm"><strong>Email:</strong> {{ props.studentEmail }}</p>
                    </div>

                    <div v-if="submitted" class="flex flex-col items-center gap-4 bg-card p-10">
                        <CheckCircle2 class="w-20 h-20 text-green-500" />
                        <h2 class="text-2xl font-bold text-center">Submission Successful!</h2>
                        <p class="text-muted-foreground text-center text-sm">
                            You have successfully submitted your activity.
                        </p>
                    </div>

                    <form v-else @submit.prevent="submit">
                        <div class="flex flex-col gap-6">
                            <div class="grid gap-2">
                                <Label for="code_content">
                                    <Code class="inline w-4 h-4 mr-1" />
                                    Your Code ({{ props.languageText }})
                                </Label>
                                <Textarea id="code_content" v-model="form.code_content"
                                    placeholder="Write your code here..." class="font-mono min-h-[400px] resize-y"
                                    spellcheck="false" />
                                <InputError :message="form.errors.code_content" />
                                <p class="text-xs text-muted-foreground">
                                    Make sure your code is complete and follows the activity requirements.
                                </p>
                            </div>

                            <Button type="submit" class="w-full" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                Submit Code
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add custom styles for code editor appearance */
#code_content {
    tab-size: 4;
    -moz-tab-size: 4;
}
</style>
