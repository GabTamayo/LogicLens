import { formatDistanceToNow, parseISO, differenceInDays } from 'date-fns';
import { Clock, AlertCircle } from 'lucide-vue-next';
import type { Component } from 'vue';

export interface DeadlineStatus {
    type: 'expired' | 'urgent' | 'active' | 'none';
    class: string;
    icon: Component;
}

export function useDeadline() {
    /**
     * Format a deadline as relative time (e.g., "in 2 hours", "2 days ago")
     */
    function formatRelativeDeadline(expires_at: string | null): string {
        if (!expires_at) return 'No deadline';
        const date = parseISO(expires_at);
        return formatDistanceToNow(date, { addSuffix: true });
    }

    /**
     * Format a deadline as absolute date and time
     */
    function formatExpiresAt(expires_at: string | null): string {
        if (!expires_at) return '';
        const date = new Date(expires_at);
        return new Intl.DateTimeFormat('en-GB', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(date);
    }

    /**
     * Get deadline status based on hours until deadline
     * Used for Activities/Show.vue
     */
    function getDeadlineStatus(expires_at: string | null): DeadlineStatus | null {
        if (!expires_at) return null;

        const now = new Date();
        const deadline = new Date(expires_at);
        const hoursUntilDeadline = (deadline.getTime() - now.getTime()) / (1000 * 60 * 60);

        if (hoursUntilDeadline < 0) {
            return {
                type: 'expired',
                class: 'text-red-600 dark:text-red-400',
                icon: AlertCircle
            };
        } else if (hoursUntilDeadline < 24) {
            return {
                type: 'urgent',
                class: 'text-orange-600 dark:text-orange-400',
                icon: AlertCircle
            };
        } else {
            return {
                type: 'active',
                class: 'text-muted-foreground',
                icon: Clock
            };
        }
    }

    /**
     * Get deadline urgency based on days until deadline
     * Used for DashboardSidebar.vue
     */
    function getDeadlineUrgency(expires_at: string | null): 'overdue' | 'urgent' | 'soon' | 'normal' | 'none' {
        if (!expires_at) return 'none';

        const days = differenceInDays(parseISO(expires_at), new Date());

        if (days < 1) return 'overdue';
        if (days <= 2) return 'urgent';
        if (days <= 5) return 'soon';
        return 'normal';
    }

    /**
     * Get color class for deadline urgency
     */
    function getUrgencyColor(urgency: 'overdue' | 'urgent' | 'soon' | 'normal' | 'none'): string {
        switch (urgency) {
            case 'overdue':
                return 'text-red-600 dark:text-red-400';
            case 'urgent':
                return 'text-orange-600 dark:text-orange-400';
            case 'soon':
                return 'text-yellow-600 dark:text-yellow-400';
            default:
                return 'text-muted-foreground';
        }
    }

    return {
        formatRelativeDeadline,
        formatExpiresAt,
        getDeadlineStatus,
        getDeadlineUrgency,
        getUrgencyColor,
    };
}
