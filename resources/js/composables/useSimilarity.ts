import { AlertTriangle, CheckCircle2, TrendingUp } from 'lucide-vue-next';

export interface SimilarityBadge {
    variant: 'destructive' | 'customOrange' | 'customYellow' | 'outline';
    label: string;
    class: string;
    icon: any;
    bg: string;
}

export function useSimilarity() {
    const getSimilarityBadge = (score: number): SimilarityBadge => {
        if (score >= 0.9) {
            return {
                variant: 'destructive',
                label: 'Very High',
                class: '',
                bg: '',
                icon: AlertTriangle,
            };
        }
        if (score >= 0.85) {
            return {
                variant: 'customOrange',
                label: 'High',
                class: '',
                bg: '',
                icon: AlertTriangle,
            };
        }
        if (score >= 0.75) {
            return {
                variant: 'customYellow',
                label: 'Moderate',
                class: '',
                bg: '',
                icon: TrendingUp,
            };
        }
        return {
            variant: 'outline',
            label: 'Low',
            class: 'text-green-700 dark:text-green-400',
            bg: 'bg-green-50 dark:bg-green-950/30',
            icon: CheckCircle2,
        };
    };

    const getSimilarityBorder = (score: number): string => {
        if (score >= 0.9) return 'border-red-500 bg-red-500/10';
        if (score >= 0.85) return 'border-orange-500 bg-orange-500/10';
        if (score >= 0.75) return 'border-yellow-500 bg-yellow-500/10';
        return 'border-muted-foreground/40 bg-muted/20';
    };

    const getSimilarityTextColor = (score: number): string => {
        if (score >= 0.9) return 'text-red-600 dark:text-red-400';
        if (score >= 0.85) return 'text-orange-600 dark:text-orange-400';
        if (score >= 0.75) return 'text-yellow-600 dark:text-yellow-400';
        return 'text-muted-foreground';
    };

    const formatScore = (score: number): string => (score * 100).toFixed(2);
    const formatScoreList = (score: number): string => (score * 100).toFixed(0);

    return {
        getSimilarityBadge,
        getSimilarityBorder,
        getSimilarityTextColor,
        formatScore,
        formatScoreList,
    };
}
