import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(urlToCheck: NonNullable<InertiaLinkProps['href']>, currentUrl: string) {
    const target = toUrl(urlToCheck)

    // Strip query parameters and hash from both URLs for comparison
    const currentPath = currentUrl.split('?')[0].split('#')[0]
    const targetPath = target.split('?')[0].split('#')[0]

    if (currentPath === targetPath) return true

    return currentPath.startsWith(targetPath + '/')
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
