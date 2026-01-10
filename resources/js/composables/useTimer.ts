import { Timer } from '@/utils/Timer';
import { useTimestamp } from '@vueuse/core';
import { computed, type Ref } from 'vue';

export function useTimer(endingAt: Ref<string | null | undefined>, hasSubmitted?: Ref<boolean>) {
    const now = useTimestamp({ interval: 1000 });

    // Create a reactive timer instance
    const timer = computed(() => new Timer(endingAt.value, now.value));

    // Hide timer if already submitted
    const shouldHide = computed(() => hasSubmitted?.value === true);

    const timeRemaining = computed(() => {
        if (shouldHide.value) {
            return null;
        }
        return timer.value.getTimeRemaining();
    });

    const formattedTimeRemaining = computed(() => {
        if (shouldHide.value) {
            return null;
        }
        return timer.value.formatTimeRemaining();
    });

    const isTimeWarning = computed(() => {
        if (shouldHide.value) {
            return false;
        }
        return timer.value.isTimeWarning();
    });

    const isTimeCritical = computed(() => {
        if (shouldHide.value) {
            return false;
        }
        return timer.value.isTimeCritical();
    });

    const hasTimerExpired = computed(() => {
        if (shouldHide.value) {
            return false;
        }
        return timer.value.hasExpired();
    });

    return {
        timeRemaining,
        formattedTimeRemaining,
        isTimeWarning,
        isTimeCritical,
        hasTimerExpired,
    };
}
