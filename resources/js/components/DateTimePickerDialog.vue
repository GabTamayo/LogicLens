<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { ScrollArea } from '@/components/ui/scroll-area';
import type { DateValue } from '@internationalized/date';
import { ref, watch } from 'vue';

const props = defineProps<{
    modelValue: Date | null;
    linkId?: string;
}>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: Date | null): void;
    (e: 'save', payload: { linkId: string; date: Date }): void;
}>();
const calendarValue = ref<DateValue | null>(null);
const selectedDateTime = ref<Date | null>(props.modelValue);
watch(
    () => props.modelValue,
    (newValue) => {
        selectedDateTime.value = newValue;
    },
);
const resetDateTime = () => {
    selectedDateTime.value = null;
    calendarValue.value = null;
    emit('update:modelValue', null);
};
const updateCalendarValue = (value: DateValue) => {
    const currentTime = selectedDateTime.value || new Date();
    const newDate = new Date(value.year, value.month - 1, value.day, currentTime.getHours(), currentTime.getMinutes());
    selectedDateTime.value = newDate;
    emit('update:modelValue', newDate);
    calendarValue.value = value;
};
function handleTimeChange(type: 'hour' | 'minute', value: number) {
    const currentDate = selectedDateTime.value || new Date();
    const newDate = new Date(currentDate);
    if (type === 'hour') {
        newDate.setHours(value);
    } else {
        newDate.setMinutes(value);
    }
    selectedDateTime.value = newDate;
    emit('update:modelValue', newDate);
}
function handleSave() {
    if (selectedDateTime.value && props.linkId) {
        emit('save', {
            linkId: props.linkId,
            date: selectedDateTime.value,
        });
    }
}
const hours = Array.from({ length: 24 }, (_, i) => i);
const minutes = Array.from({ length: 12 }, (_, i) => i * 5);
</script>

<template>
    <div class="flex">
        <Calendar v-model="calendarValue" @update:modelValue="updateCalendarValue" initial-focus />

        <div class="flex h-[300px] w-full flex-row divide-x">
            <ScrollArea class="w-auto sm:w-13">
                <div class="flex flex-col">
                    <Button
                        v-for="hour in hours"
                        :key="hour"
                        size="icon"
                        :variant="selectedDateTime?.getHours() === hour ? 'default' : 'ghost'"
                        class="aspect-square w-full shrink-0"
                        type="button"
                        @click="handleTimeChange('hour', hour)"
                    >
                        {{ hour }}
                    </Button>
                </div>
            </ScrollArea>

            <ScrollArea class="w-auto sm:w-13">
                <div class="flex flex-col">
                    <Button
                        v-for="minute in minutes"
                        :key="minute"
                        size="icon"
                        :variant="selectedDateTime?.getMinutes() === minute ? 'default' : 'ghost'"
                        class="aspect-square w-full shrink-0"
                        type="button"
                        @click="handleTimeChange('minute', minute)"
                    >
                        {{ minute.toString().padStart(2, '0') }}
                    </Button>
                </div>
            </ScrollArea>
        </div>
    </div>
    <div class="flex justify-end gap-2 p-2">
        <Button variant="outline" type="button" @click="resetDateTime"> Reset </Button>
        <Button type="button" @click="handleSave"> Save </Button>
    </div>
</template>
