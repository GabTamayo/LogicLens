<script setup lang="ts">
import { VueMonacoEditor } from '@guolao/vue-monaco-editor';
import { computed, ref } from 'vue';

interface Props {
    modelValue: string;
    language: string;
    readonly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    readonly: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editorRef = ref();

const options = {
    automaticLayout: true,
    formatOnType: true,
    formatOnPaste: true,
    minimap: { enabled: true },
    fontSize: 14,
    lineNumbers: 'on',
    scrollBeyondLastLine: false,
    wordWrap: 'on',
    theme: 'vs-dark',
    readOnly: props.readonly,
};

const handleMount = (editor: any) => {
    editorRef.value = editor;
};

const handleChange = (value: string) => {
    emit('update:modelValue', value);
};

// Map Laravel language enum to Monaco language identifiers
const getMonacoLanguage = computed(() => {
    const languageMap: Record<string, string> = {
        java: 'java',
        python: 'python',
    };
    return languageMap[props.language] || 'plaintext';
});
</script>

<template>
    <VueMonacoEditor
        :value="modelValue"
        :language="getMonacoLanguage"
        :options="options"
        class="h-full w-full"
        @mount="handleMount"
        @update:value="handleChange"
    />
</template>
