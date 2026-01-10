export class Timer {
    private endingAt: string | null | undefined;
    private currentTime: number;

    constructor(endingAt: string | null | undefined, currentTime: number) {
        this.endingAt = endingAt;
        this.currentTime = currentTime;
    }

    /**
     * Get remaining time in seconds, returns null if timer shouldn't be shown
     */
    getTimeRemaining(): number | null {
        if (!this.endingAt) {
            return null;
        }

        const endTime = new Date(this.endingAt).getTime();
        const remaining = Math.max(0, Math.floor((endTime - this.currentTime) / 1000));

        // Hide if time has expired
        if (remaining === 0) {
            return null;
        }

        return remaining;
    }

    /**
     * Format remaining seconds as HH:MM:SS or MM:SS
     */
    formatTimeRemaining(): string | null {
        const timeRemaining = this.getTimeRemaining();

        if (timeRemaining === null) {
            return null;
        }

        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = timeRemaining % 60;

        if (hours > 0) {
            return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        return `${minutes}:${String(seconds).padStart(2, '0')}`;
    }

    /**
     * Check if timer is in warning state (1-5 minutes remaining)
     */
    isTimeWarning(): boolean {
        const timeRemaining = this.getTimeRemaining();

        if (timeRemaining === null) {
            return false;
        }

        return timeRemaining <= 300 && timeRemaining > 60;
    }

    /**
     * Check if timer is in critical state (≤1 minute remaining)
     */
    isTimeCritical(): boolean {
        const timeRemaining = this.getTimeRemaining();

        if (timeRemaining === null) {
            return false;
        }

        return timeRemaining <= 60;
    }

    /**
     * Check if timer has expired (reached 0)
     */
    hasExpired(): boolean {
        const timeRemaining = this.getTimeRemaining();
        return timeRemaining === 0;
    }
}
