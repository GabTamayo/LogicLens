export default {
    content: [
        './node_modules/@inertiaui/modal-vue/src/**/*.{js,vue}',
        // other paths...
    ],
    theme: {
        extend: {
            typography: {
                DEFAULT: {
                    css: {
                        '--tw-prose-code': 'var(--color-primary)',
                        '--tw-prose-bullets': 'var(--color-gray-600)',
                        '--tw-prose-counters': 'var(--color-gray-600)',
                        '--tw-prose-invert-code': 'var(--color-primary)',
                        '--tw-prose-invert-bullets': 'var(--color-white)',
                        '--tw-prose-invert-counters': 'var(--color-white)',
                        '--tw-prose-invert-pre-bg': 'var(--color-muted)',

                        code: {
                            backgroundColor: 'var(--color-muted)',
                            borderRadius: '0.5rem',
                            padding: '0.15rem 0.5rem',
                            fontSize: '0.875em',
                            fontWeight: '600',
                            fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
                        },
                        'code::before': {
                            content: '""',
                        },
                        'code::after': {
                            content: '""',
                        },
                        // Add pre styling
                        pre: {
                            backgroundColor: 'var(--tw-prose-pre-bg)',
                            color: 'var(--tw-prose-pre-code)',
                            borderRadius: '0.5rem',
                            padding: '1rem',
                        },
                        'pre code': {
                            backgroundColor: 'transparent',
                            color: 'inherit',
                            padding: '0',
                            borderRadius: '0',
                            fontWeight: 'normal',
                        },
                        table: {
                            borderCollapse: 'collapse',
                            tableLayout: 'fixed',
                            width: '100%',
                            margin: '0',
                            overflow: 'hidden',
                        },
                        'td, th': {
                            border: '1px solid var(--color-border)',
                            boxSizing: 'border-box',
                            minWidth: '1em',
                            padding: '0.5rem 0.75rem',
                            position: 'relative',
                            verticalAlign: 'top',
                        },
                        th: {
                            backgroundColor: 'var(--color-muted)',
                            fontWeight: '600',
                            textAlign: 'left',
                        },
                        thead: {
                            borderBottom: '2px solid var(--color-border)',
                        },
                        '.column-resize-handle': {
                            position: 'absolute',
                            right: '-2px',
                            top: '0',
                            bottom: '-2px',
                            width: '4px',
                            backgroundColor: 'var(--color-primary)',
                            pointerEvents: 'none',
                            cursor: 'col-resize',
                        },
                        // Selected cell highlight
                        '.selectedCell': {
                            backgroundColor: 'color-mix(in srgb, var(--color-primary) 20%, transparent)',
                            outline: '2px solid var(--color-primary)',
                            outlineOffset: '-1px',
                        },
                    },
                },
            },
            cursor: {
                'col-resize': 'col-resize',
            },
        },
    },
};
