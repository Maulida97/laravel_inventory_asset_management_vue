import { cva } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80',
                secondary: 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
                destructive: 'border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80',
                outline: 'text-foreground',
                success: 'border-transparent bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                warning: 'border-transparent bg-amber-500/15 text-amber-600 dark:text-amber-400',
                info: 'border-transparent bg-sky-500/15 text-sky-600 dark:text-sky-400',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);