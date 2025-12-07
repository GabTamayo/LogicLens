<script setup lang="ts">
import { Modal } from '@inertiaui/modal-vue';
import { Bold, Italic, UnderlineIcon, Strikethrough, Code, Heading1, Heading2, Heading3, List, ListOrdered, SquareDashedBottomCode, Quote, Minus, Undo, Redo } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import InputError from '@/components/InputError.vue';
import { toast } from 'vue-sonner';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select'
import Underline from '@tiptap/extension-underline'
import { EditorContent, useEditor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'

defineProps<{ languages: Record<string, string> }>();
const form = useForm({
    title: '',
    language: '',
    content: '',
});
const editor = useEditor({
    editorProps: {
        attributes: {
            class: 'p-4 min-h-[21rem] max-h-[21rem] max-w-none overflow-y-auto prose dark:prose-invert'
        },
    },
    content: form.content || '',
    extensions: [StarterKit, Underline],
    onUpdate: ({ editor }) => {
        form.content = editor.getHTML();
    },
})

function submit(close: () => void) {
    form.post('/activities', {
        onSuccess: () => {
            toast.success('Activity created successfully!');
            close();
        },
        onError: () => {
            toast.error('Failed to create activity. Please try again.');
        },
    });
}
</script>

<template>
    <Modal max-width="7xl" position="top" v-slot="{ close }"
        panel-classes="bg-white rounded dark:bg-[hsl(240.02_9.66%_1.01%)]">
        <Form class="space-y-6" @submit="submit(close)">
            <FormField name="title">
                <FormItem>
                    <div class="mb-4">
                        <h1 class="font-bold text-lg cursor-default">Add Activity</h1>
                        <FormDescription>
                            Add an activity to generate submission links. Click Save once you're done.
                        </FormDescription>
                    </div>
                    <FormLabel>Title</FormLabel>
                    <FormControl>
                        <Input type="text" v-model="form.title" />
                    </FormControl>
                    <InputError :message="form.errors.title" />
                </FormItem>
            </FormField>
            <FormField name="language">
                <FormItem>
                    <FormLabel>Programming Language</FormLabel>
                    <FormControl>
                        <Select v-model="form.language">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Select Programming Language" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Programming Language</SelectLabel>
                                    <SelectItem v-for="(label, value) in languages" :key="value" :value="value">
                                        {{ label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </FormControl>
                    <InputError :message="form.errors.language" />
                </FormItem>
            </FormField>
            <FormField name="content">
                <FormItem>
                    <FormLabel>Content</FormLabel>
                    <FormControl>
                        <div class="border">
                        <section v-if="editor" class="buttons flex items-center flex-wrap gap-x-4 border-b p-4">
                            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                                :disabled="!editor.can().chain().focus().toggleBold().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('bold') }" class="p-1 cursor-pointer">
                                <Bold class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                                :disabled="!editor.can().chain().focus().toggleItalic().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('italic') }"
                                class="p-1 cursor-pointer">
                                <Italic class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('underline') }"
                                class="p-1 cursor-pointer">
                                <UnderlineIcon class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleStrike().run()"
                                :disabled="!editor.can().chain().focus().toggleStrike().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('strike') }"
                                class="p-1 cursor-pointer">
                                <Strikethrough class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleCode().run()"
                                :disabled="!editor.can().chain().focus().toggleCode().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('code') }" class="p-1 cursor-pointer">
                                <Code class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('heading', { level: 1 }) }"
                                class="p-1 cursor-pointer">
                                <Heading1 class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('heading', { level: 2 }) }"
                                class="p-1 cursor-pointer">
                                <Heading2 class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('heading', { level: 3 }) }"
                                class="p-1 cursor-pointer">
                                <Heading3 class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('bulletList') }"
                                class="p-1 cursor-pointer">
                                <List class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('orderedList') }"
                                class="p-1 cursor-pointer">
                                <ListOrdered class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('codeBlock') }"
                                class="p-1 cursor-pointer">
                                <SquareDashedBottomCode class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('blockquote') }"
                                class="p-1 cursor-pointer">
                                <Quote class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().setHorizontalRule().run()"
                                class="p-1 cursor-pointer">
                                <Minus class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().undo().run()"
                                :disabled="!editor.can().chain().focus().undo().run()" class="p-1 cursor-pointer">
                                <Undo class="h-4 w-4" />
                            </button>
                            <button type="button" @click="editor.chain().focus().redo().run()"
                                :disabled="!editor.can().chain().focus().redo().run()" class="p-1 cursor-pointer">
                                <Redo class="h-4 w-4" />
                            </button>
                        </section>
                        <EditorContent :editor="editor" />
                    </div>
                    </FormControl>
                    <InputError :message="form.errors.content" />
                </FormItem>
            </FormField>

            <Button type="submit" :disabled="form.processing" class="cursor-pointer">
                Save
            </Button>
        </Form>
    </Modal>
</template>
