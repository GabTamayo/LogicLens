export interface LanguageConfig {
    colors: string;
    logo: string | null;
}

const LANGUAGE_CONFIGS: Record<string, LanguageConfig> = {
    Java: {
        colors: 'bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 border-red-200 dark:border-red-800',
        logo: '/images/java-logo-png.png',
    },
    Python: {
        colors: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800',
        logo: '/images/python-logo-png.png',
    },
};

// Support lowercase language keys for compatibility
const LANGUAGE_CONFIGS_LOWERCASE: Record<string, LanguageConfig> = {
    java: {
        colors: 'bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 border-red-200 dark:border-red-800',
        logo: '/images/java-logo-png.png',
    },
    python: {
        colors: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800',
        logo: '/images/python-logo-png.png',
    },
};

const DEFAULT_CONFIG: LanguageConfig = {
    colors: 'bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border-blue-200 dark:border-blue-800',
    logo: null,
};

export function useLanguage() {
    const getLanguageColor = (language: string): string => {
        return LANGUAGE_CONFIGS[language]?.colors
            ?? LANGUAGE_CONFIGS_LOWERCASE[language?.toLowerCase()]?.colors
            ?? DEFAULT_CONFIG.colors;
    };

    const getLanguageLogo = (language: string): string | null => {
        return LANGUAGE_CONFIGS[language]?.logo
            ?? LANGUAGE_CONFIGS_LOWERCASE[language?.toLowerCase()]?.logo
            ?? DEFAULT_CONFIG.logo;
    };

    const getLanguageConfig = (language: string): LanguageConfig => {
        return LANGUAGE_CONFIGS[language]
            ?? LANGUAGE_CONFIGS_LOWERCASE[language?.toLowerCase()]
            ?? DEFAULT_CONFIG;
    };

    return {
        getLanguageColor,
        getLanguageLogo,
        getLanguageConfig,
    };
}
