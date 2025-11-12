<script setup lang="ts">
import { ref, watch } from 'vue'
import { format } from 'date-fns'

import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area'
import { CalendarIcon } from 'lucide-vue-next'
import type { DateValue } from '@internationalized/date'

const props = defineProps<{
    modelValue: Date | null
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: Date | null): void
}>()

const open = ref(false)
const calendarValue = ref<DateValue | null>(null)
const selectedDateTime = ref<Date | null>(props.modelValue)

watch(() => props.modelValue, (newValue) => {
    selectedDateTime.value = newValue
})

const resetDateTime = () => {
    selectedDateTime.value = null
    calendarValue.value = null
    emit('update:modelValue', null)
}
const updateCalendarValue = (value: DateValue) => {
    const currentTime = selectedDateTime.value || new Date()
    const newDate = new Date(
        value.year,
        value.month - 1,
        value.day,
        currentTime.getHours(),
        currentTime.getMinutes()
    )
    selectedDateTime.value = newDate
    emit('update:modelValue', newDate)
    calendarValue.value = value
}

function handleTimeChange(type: 'hour' | 'minute', value: number) {
    const currentDate = selectedDateTime.value || new Date()
    const newDate = new Date(currentDate)
    type === 'hour' ? newDate.setHours(value) : newDate.setMinutes(value)
    selectedDateTime.value = newDate
    emit('update:modelValue', newDate)
}

const hours = Array.from({ length: 24 }, (_, i) => i)
const minutes = Array.from({ length: 12 }, (_, i) => i * 5)
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="outline" class="w-full lg:w-50 pl-3 text-left font-normal">
                <span v-if="selectedDateTime">{{ format(selectedDateTime, 'MM/dd/yyyy HH:mm') }}</span>
                <span v-else class="text-muted-foreground">MM/DD/YYYY HH:mm</span>
                <CalendarIcon class="ml-auto h-4 w-4 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-auto p-0">
            <div class="sm:flex">
                <Calendar v-model="calendarValue" @update:modelValue="updateCalendarValue" initial-focus />

                <div class="flex flex-col sm:flex-row sm:h-[300px] divide-y sm:divide-y-0 sm:divide-x">
                    <!-- Hours -->
                    <ScrollArea class="w-64 sm:w-auto">
                        <div class="flex sm:flex-col p-2">
                            <Button v-for="hour in hours" :key="hour" size="icon"
                                :variant="selectedDateTime?.getHours() === hour ? 'default' : 'ghost'"
                                class="sm:w-full shrink-0 aspect-square" type="button"
                                @click="handleTimeChange('hour', hour)">
                                {{ hour }}
                            </Button>
                        </div>
                        <ScrollBar orientation="horizontal" class="sm:hidden" />
                    </ScrollArea>

                    <ScrollArea class="w-64 sm:w-auto">
                        <div class="flex sm:flex-col p-2">
                            <Button v-for="minute in minutes" :key="minute" size="icon"
                                :variant="selectedDateTime?.getMinutes() === minute ? 'default' : 'ghost'"
                                class="sm:w-full shrink-0 aspect-square" type="button"
                                @click="handleTimeChange('minute', minute)">
                                {{ minute.toString().padStart(2, '0') }}
                            </Button>
                        </div>
                        <ScrollBar orientation="horizontal" class="sm:hidden" />
                    </ScrollArea>
                </div>
            </div>
            <div class="flex justify-end p-2">
                <Button variant="outline" size="sm" type="button" @click="resetDateTime">
                    Reset
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
