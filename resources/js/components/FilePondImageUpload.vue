<script setup lang="ts">
import { ref } from 'vue';
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';

const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageTransform
);

interface Props {
    acceptedFileTypes?: string[];
    maxFileSize?: string;
    allowImageCrop?: boolean;
    imageCropAspectRatio?: string;
    allowImageResize?: boolean;
    imageResizeTargetWidth?: number;
    imageResizeTargetHeight?: number;
    labelIdle?: string;
}

withDefaults(defineProps<Props>(), {
    acceptedFileTypes: () => ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'],
    maxFileSize: '5MB',
    allowImageCrop: false,
    imageCropAspectRatio: '1:1',
    allowImageResize: true,
    imageResizeTargetWidth: 1200,
    imageResizeTargetHeight: undefined,
    labelIdle: 'Drop your image here or <span class="filepond--label-action">Browse</span>',
});

const emit = defineEmits<{
    fileProcessed: [file: File, base64: string];
    fileRemoved: [];
}>();

const pond = ref<any>(null);

const handleAddFile = (error: any, file: any) => {
    if (error) {
        console.error('FilePond add file error:', error);
        return;
    }

    const actualFile = file.file as File;

    // Get the base64 data from the file
    const reader = new FileReader();
    reader.onload = (e) => {
        const base64 = e.target?.result as string;
        emit('fileProcessed', actualFile, base64);
    };
    reader.readAsDataURL(actualFile);
};

const handleRemoveFile = () => {
    emit('fileRemoved');
};

// Method to programmatically clear the pond
const clear = () => {
    pond.value?.removeFiles();
};

defineExpose({
    clear,
});
</script>

<template>
    <div class="filepond-wrapper">
        <FilePond
            ref="pond"
            name="image"
            :accepted-file-types="acceptedFileTypes"
            :max-file-size="maxFileSize"
            :allow-image-crop="allowImageCrop"
            :image-crop-aspect-ratio="imageCropAspectRatio"
            :allow-image-resize="allowImageResize"
            :image-resize-target-width="imageResizeTargetWidth"
            :image-resize-target-height="imageResizeTargetHeight"
            :label-idle="labelIdle"
            :allow-multiple="false"
            :server="null"
            credits="false"
            @addfile="handleAddFile"
            @removefile="handleRemoveFile"
        />
    </div>
</template>

<style scoped>
.filepond-wrapper :deep(.filepond--root) {
    font-family: inherit;
}

.filepond-wrapper :deep(.filepond--drop-label) {
    color: hsl(var(--muted-foreground));
}

.filepond-wrapper :deep(.filepond--label-action) {
    color: hsl(var(--primary));
    text-decoration: underline;
}

.filepond-wrapper :deep(.filepond--panel-root) {
    background-color: hsl(var(--background));
    border: 2px dashed hsl(var(--border));
    border-radius: 0.5rem;
}

.filepond-wrapper :deep(.filepond--item-panel) {
    background-color: hsl(var(--muted));
}

.filepond-wrapper :deep(.filepond--drip-blob) {
    background-color: hsl(var(--primary));
}
</style>
