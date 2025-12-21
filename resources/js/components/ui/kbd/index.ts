import type { VariantProps } from 'class-variance-authority';
import { cva } from 'class-variance-authority';

export { default as Kbd } from './Kbd.vue';

export const kbdVariants = cva(
    'inline-flex items-center justify-center rounded border font-mono font-medium whitespace-nowrap select-none',
    {
        variants: {
            variant: {
                default: 'border-border bg-muted text-muted-foreground shadow-sm',
                outline: 'border-border bg-background text-foreground',
                solid: 'border-transparent bg-foreground text-background shadow-sm',
            },
            size: {
                default: 'h-5 min-w-5 px-1.5 text-xs',
                sm: 'h-4 min-w-4 px-1 text-[10px]',
                lg: 'h-6 min-w-6 px-2 text-sm',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export type KbdVariants = VariantProps<typeof kbdVariants>;
