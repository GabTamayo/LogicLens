export interface ScoreDisplay {
    text: string;
    colorClass: string;
    percentage: number;
}

export function useScore() {
    const getScoreDisplay = (score: number | null, totalScore: number | null): ScoreDisplay => {
        if (score === null || totalScore === null) {
            return {
                text: 'Not graded',
                colorClass: 'text-muted-foreground',
                percentage: 0,
            };
        }

        const percentage = totalScore > 0 ? (score / totalScore) * 100 : 0;
        const colorClass = percentage >= 70 ? 'text-green-600 dark:text-green-500' :
                           percentage >= 50 ? 'text-yellow-600 dark:text-yellow-500' :
                           'text-red-600 dark:text-red-500';

        return {
            text: `${score}/${totalScore}`,
            colorClass,
            percentage,
        };
    };

    const getScoreColorClass = (score: number | null, totalScore: number | null): string => {
        return getScoreDisplay(score, totalScore).colorClass;
    };

    const getScoreBackgroundClass = (score: number | null, totalScore: number | null): string => {
        if (score === null || totalScore === null) {
            return 'bg-slate-500 dark:bg-slate-600';
        }

        const percentage = totalScore > 0 ? (score / totalScore) * 100 : 0;
        return percentage >= 70 ? 'bg-green-600 dark:bg-green-700' :
               percentage >= 50 ? 'bg-yellow-600 dark:bg-yellow-700' :
               'bg-red-700 dark:bg-red-800';
    };

    return {
        getScoreDisplay,
        getScoreColorClass,
        getScoreBackgroundClass,
    };
}
