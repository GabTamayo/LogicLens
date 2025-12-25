<script setup lang="ts">
import { Bold, Italic, Grid2X2, Grid2X2X, Grid2x2Plus, ListCollapse, ListStart, RectangleHorizontal, RectangleVertical, UnderlineIcon, Strikethrough, SquareCode, Heading1, Heading2, Heading3, List, ListOrdered, Quote, Minus, Undo, Redo, AlignLeft, AlignCenter, AlignRight, AlignJustify, BetweenHorizonalStart, BetweenHorizonalEnd, BetweenVerticalStart, BetweenVerticalEnd, TableCellsMerge, TableCellsSplit, ChevronDown, ImageIcon } from 'lucide-vue-next';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import Image from '@tiptap/extension-image';
import { TableCell, TableKit } from '@tiptap/extension-table';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { computed, ref, watch, onBeforeUnmount } from 'vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import FilePondImageUpload from '@/components/FilePondImageUpload.vue';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isImageDialogOpen = ref(false);
const imagePreview = ref<string>('');
const filePondRef = ref<InstanceType<typeof FilePondImageUpload> | null>(null);

const editor = useEditor({
    editorProps: {
        attributes: {
            class: 'p-4 min-h-[28rem] max-h-[28rem] max-w-none overflow-y-auto prose dark:prose-invert',
        },
    },
    content: props.modelValue || '',
    extensions: [
        StarterKit,
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Image.configure({
            inline: true,
            allowBase64: true,
            resize: {
                enabled: true,
                alwaysPreserveAspectRatio: true,
            },
            HTMLAttributes: {
                class: 'max-w-full h-auto',
            },
        }),
        TableKit.configure({
            table: {
                resizable: true,
            },
            tableCell: false,
        }),
        TableCell,
    ],
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

const isInsideTable = computed(() => editor.value?.isActive('table') ?? false);

const insertTable = (): void => {
    if (!editor.value || isInsideTable.value) {
        return;
    }

    editor.value.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
};

const openImageDialog = (): void => {
    isImageDialogOpen.value = true;
};

const handleFileProcessed = (file: File, base64: string): void => {
    imagePreview.value = base64;
};

const handleFileRemoved = (): void => {
    imagePreview.value = '';
};

const insertImage = (): void => {
    if (!editor.value || !imagePreview.value) {
        return;
    }

    editor.value.chain().focus().setImage({
        src: imagePreview.value,
        alt: '',
    }).run();

    // Reset and close dialog
    filePondRef.value?.clear();
    imagePreview.value = '';
    isImageDialogOpen.value = false;
};

watch(() => props.modelValue, (newValue) => {
    if (editor.value && editor.value.getHTML() !== newValue) {
        editor.value.commands.setContent(newValue || '');
    }
});

// Hide tooltip portal content while the image dialog is open so tooltips don't
// appear or block dialog inputs. We toggle a class on <body> which targets
// the tooltip portal elements rendered outside this component.
watch(isImageDialogOpen, (isOpen) => {
    try {
        document.body.classList.toggle('no-tooltips', Boolean(isOpen));
    } catch (e) {
        // ignore (SSR or no document)
    }
});

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.body.classList.remove('no-tooltips');
    }
});
</script>

<template>
    <div class="border">
        <section v-if="editor" class="border-b p-4">
            <TooltipProvider>
                <div class="buttons flex items-center flex-wrap gap-x-4">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                                :disabled="!editor.can().chain().focus().toggleBold().run()" :class="[
                                    { 'bg-gray-200 rounded': editor.isActive('bold') },
                                    'p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default',
                                ]">
                                <Bold class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Bold
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                                :disabled="!editor.can().chain().focus().toggleItalic().run()" :class="[
                                    { 'bg-gray-200 rounded': editor.isActive('italic') },
                                    'p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default',
                                ]">
                                <Italic class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Italic
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                                :disabled="!editor.can().chain().focus().toggleUnderline().run()" :class="[
                                    { 'bg-gray-200 rounded': editor.isActive('underline') },
                                    'p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default',
                                ]">
                                <UnderlineIcon class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Underline
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleStrike().run()"
                                :disabled="!editor.can().chain().focus().toggleStrike().run()" :class="[
                                    { 'bg-gray-200 rounded': editor.isActive('strike') },
                                    'p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default',
                                ]">
                                <Strikethrough class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Strikethrough
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('heading', { level: 1 }) }"
                                class="p-1 cursor-pointer">
                                <Heading1 class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Heading 1
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('heading', { level: 2 }) }"
                                class="p-1 cursor-pointer">
                                <Heading2 class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Heading 2
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('heading', { level: 3 }) }"
                                class="p-1 cursor-pointer">
                                <Heading3 class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Heading 3
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('bulletList') }"
                                class="p-1 cursor-pointer">
                                <List class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Bullet List
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('orderedList') }"
                                class="p-1 cursor-pointer">
                                <ListOrdered class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Ordered List
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('codeBlock') }"
                                class="p-1 cursor-pointer">
                                <SquareCode class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Code Block
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive('blockquote') }"
                                class="p-1 cursor-pointer">
                                <Quote class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Blockquote
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().setHorizontalRule().run()"
                                class="p-1 cursor-pointer">
                                <Minus class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Horizontal Rule
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().setTextAlign('left').run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive({ textAlign: 'left' }) }"
                                class="p-1 cursor-pointer">
                                <AlignLeft class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Align Left
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().setTextAlign('center').run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive({ textAlign: 'center' }) }"
                                class="p-1 cursor-pointer">
                                <AlignCenter class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Align Center
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().setTextAlign('right').run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive({ textAlign: 'right' }) }"
                                class="p-1 cursor-pointer">
                                <AlignRight class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Align Right
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().setTextAlign('justify').run()"
                                :class="{ 'bg-gray-200 rounded': editor.isActive({ textAlign: 'justify' }) }"
                                class="p-1 cursor-pointer">
                                <AlignJustify class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Justify
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="openImageDialog"
                                class="p-1 cursor-pointer">
                                <ImageIcon class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Insert Image
                        </TooltipContent>
                    </Tooltip>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button type="button" class="p-1 cursor-pointer flex items-center gap-1">
                                <Grid2X2 class="h-4 w-4" />
                                <ChevronDown class="h-3 w-3" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start">
                            <DropdownMenuLabel>Insert Table</DropdownMenuLabel>
                            <DropdownMenuItem @click="insertTable" :disabled="isInsideTable"
                                :class="[{ 'opacity-50 cursor-default': isInsideTable }]">
                                <Grid2x2Plus class="h-4 w-4 mr-2" />
                                Insert Table
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="editor.chain().focus().addColumnBefore().run()"
                                :disabled="!editor.can().addColumnBefore()">
                                <BetweenHorizonalStart class="h-4 w-4 mr-2" />
                                Add Column Before
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().addColumnAfter().run()"
                                :disabled="!editor.can().addColumnAfter()">
                                <BetweenHorizonalEnd class="h-4 w-4 mr-2" />
                                Add Column After
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().addRowBefore().run()"
                                :disabled="!editor.can().addRowBefore()">
                                <BetweenVerticalStart class="h-4 w-4 mr-2" />
                                Add Row Before
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().addRowAfter().run()"
                                :disabled="!editor.can().addRowAfter()">
                                <BetweenVerticalEnd class="h-4 w-4 mr-2" />
                                Add Row After
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="editor.chain().focus().toggleHeaderColumn().run()"
                                :disabled="!editor.can().toggleHeaderColumn()">
                                <ListCollapse class="h-4 w-4 mr-2" />
                                Toggle Header Column
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().toggleHeaderRow().run()"
                                :disabled="!editor.can().toggleHeaderRow()">
                                <ListStart class="h-4 w-4 mr-2" />
                                Toggle Header Row
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().toggleHeaderCell().run()"
                                :disabled="!editor.can().toggleHeaderCell()">
                                <RectangleHorizontal class="h-4 w-4 mr-2" />
                                Toggle Header Cell
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().mergeCells().run()"
                                :disabled="!editor.can().mergeCells()"
                                :class="['p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default']">
                                <TableCellsMerge class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Merge Cells
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().splitCell().run()"
                                :disabled="!editor.can().splitCell()"
                                :class="['p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default']">
                                <TableCellsSplit class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Split Cell
                        </TooltipContent>
                    </Tooltip>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button type="button" class="p-1 cursor-pointer flex items-center gap-1">
                                <Grid2X2X class="h-4 w-4" />
                                <ChevronDown class="h-3 w-3" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start">
                            <DropdownMenuLabel>Delete Table</DropdownMenuLabel>
                            <DropdownMenuItem @click="editor.chain().focus().deleteColumn().run()"
                                :disabled="!editor.can().deleteColumn()">
                                <RectangleVertical class="h-4 w-4 mr-2" />
                                Delete Column
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().deleteRow().run()"
                                :disabled="!editor.can().deleteRow()">
                                <RectangleHorizontal class="h-4 w-4 mr-2" />
                                Delete Row
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="editor.chain().focus().deleteTable().run()"
                                :disabled="!editor.can().deleteTable()" class="text-red-600">
                                <Grid2X2X class="h-4 w-4 mr-2" />
                                Delete Table
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().undo().run()"
                                :disabled="!editor.can().undo()"
                                :class="['p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default']">
                                <Undo class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Undo
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <button type="button" @click="editor.chain().focus().redo().run()"
                                :disabled="!editor.can().redo()"
                                :class="['p-1 cursor-pointer disabled:opacity-40 disabled:cursor-default']">
                                <Redo class="h-4 w-4" />
                            </button>
                        </TooltipTrigger>
                        <TooltipContent>
                            Redo
                        </TooltipContent>
                    </Tooltip>
                </div>
            </TooltipProvider>
        </section>
        <EditorContent :editor="editor" />

        <Dialog v-model:open="isImageDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Insert Image</DialogTitle>
                    <DialogDescription>
                        Upload an image file to insert into your content.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <FilePondImageUpload
                        ref="filePondRef"
                        @file-processed="handleFileProcessed"
                        @file-removed="handleFileRemoved"
                    />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="isImageDialogOpen = false">
                        Cancel
                    </Button>
                    <Button type="button" @click="insertImage" :disabled="!imagePreview">
                        Insert Image
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style lang="scss">
.tiptap {
    img {
        max-width: 100%;
        height: auto;
        border-radius: 0.375rem;
        display: block;

        &.ProseMirror-selectednode {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }
    }

    [data-resize-handle] {
        position: absolute;
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 2px;
        z-index: 10;

        &:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        /* Corner handles */
        &[data-resize-handle='top-left'],
        &[data-resize-handle='top-right'],
        &[data-resize-handle='bottom-left'],
        &[data-resize-handle='bottom-right'] {
            width: 8px;
            height: 8px;
        }

        &[data-resize-handle='top-left'] {
            top: -4px;
            left: -4px;
            cursor: nwse-resize;
        }

        &[data-resize-handle='top-right'] {
            top: -4px;
            right: -4px;
            cursor: nesw-resize;
        }

        &[data-resize-handle='bottom-left'] {
            bottom: -4px;
            left: -4px;
            cursor: nesw-resize;
        }

        &[data-resize-handle='bottom-right'] {
            bottom: -4px;
            right: -4px;
            cursor: nwse-resize;
        }

        /* Edge handles */
        &[data-resize-handle='top'],
        &[data-resize-handle='bottom'] {
            height: 6px;
            left: 8px;
            right: 8px;
        }

        &[data-resize-handle='top'] {
            top: -3px;
            cursor: ns-resize;
        }

        &[data-resize-handle='bottom'] {
            bottom: -3px;
            cursor: ns-resize;
        }

        &[data-resize-handle='left'],
        &[data-resize-handle='right'] {
            width: 6px;
            top: 8px;
            bottom: 8px;
        }

        &[data-resize-handle='left'] {
            left: -3px;
            cursor: ew-resize;
        }

        &[data-resize-handle='right'] {
            right: -3px;
            cursor: ew-resize;
        }
    }

    [data-resize-state='true'] [data-resize-wrapper] {
        outline: 1px solid rgba(0, 0, 0, 0.25);
        border-radius: 0.125rem;
    }

    table {
        border-collapse: collapse;
        margin: 0;
        overflow: hidden;
        table-layout: fixed;
        width: 100%;

        td,
        th {
            border: 1px solid var(--color-border);
            box-sizing: border-box;
            min-width: 1em;
            padding: 6px 8px;
            position: relative;
            vertical-align: top;
        }

        th {
            background-color: var(--color-muted);
            font-weight: bold;
            text-align: left;
        }

        .selectedCell:after {
            background: color-mix(in srgb, var(--color-primary) 20%, transparent);
            content: '';
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            pointer-events: none;
            position: absolute;
            z-index: 2;
        }

        .column-resize-handle {
            background-color: var(--color-primary);
            bottom: -2px;
            pointer-events: none;
            position: absolute;
            right: -2px;
            top: 0;
            width: 4px;
        }
    }

    &.resize-cursor {
        cursor: col-resize;
    }
}

/* When the image dialog is open, hide tooltip portal content so it cannot
   overlap or block dialog inputs. */
.no-tooltips [data-slot="tooltip-content"],
.no-tooltips [data-slot="tooltip"] {
    display: none !important;
    pointer-events: none !important;
}
</style>
