import { onBeforeUnmount, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import axios from 'axios';

interface ExamSecurityOptions {
    token: string;
    enabled: boolean;
    requireFullscreen?: boolean;
    blockKeyboardShortcuts?: boolean;
}

export function useExamSecurity(options: ExamSecurityOptions) {
    const isMonitoring = ref(false);
    const isFullscreen = ref(false);
    const tabSwitchCount = ref(0);
    const fullscreenExitCount = ref(0);
    const keyboardViolationCount = ref(0);

    // Log violation to backend
    const logViolation = async (type: string, details: string) => {
        if (!options.enabled) return;

        try {
            await axios.post(`/student/submission/${options.token}/violation`, {
                violation_type: type,
                details,
                timestamp: new Date().toISOString(),
            });
        } catch (error) {
            console.error('Failed to log violation:', error);
        }
    };

    // Handle tab/window visibility changes
    const handleVisibilityChange = () => {
        if (document.hidden && options.enabled && isMonitoring.value) {
            tabSwitchCount.value++;
            logViolation('tab_switch', 'User switched to another tab or window');

            toast.warning('Warning: Tab Switch Detected', {
                description: 'Please stay on this page. This activity is being monitored.',
            });
        }
    };

    // Handle window blur (when user clicks outside the window)
    const handleWindowBlur = () => {
        if (!options.enabled || !isMonitoring.value) return;

        // Check if the document is not hidden (window is still visible but not focused)
        // This catches cases when window is not maximized and user clicks outside
        if (!document.hidden) {
            tabSwitchCount.value++;
            logViolation('window_blur', 'User clicked outside the window or switched applications');

            toast.warning('Warning: Focus Lost', {
                description: 'Please keep focus on this window. This activity is being monitored.',
            });
        }
    };

    // Handle keyboard shortcuts
    const handleKeyDown = (event: KeyboardEvent) => {
        if (!options.blockKeyboardShortcuts) return;

        const blocked = [
            // Alt+Tab (Windows/Linux)
            event.altKey && event.key === 'Tab',
            // Ctrl+Tab (switch tabs)
            event.ctrlKey && event.key === 'Tab',
            // Cmd+Tab (Mac)
            event.metaKey && event.key === 'Tab',
            // F12 (DevTools)
            event.key === 'F12',
            // Ctrl+Shift+I (DevTools)
            (event.ctrlKey || event.metaKey) && event.shiftKey && event.key === 'I',
            // Ctrl+Shift+C (Inspect Element)
            (event.ctrlKey || event.metaKey) && event.shiftKey && event.key === 'C',
            // Ctrl+Shift+J (Console)
            (event.ctrlKey || event.metaKey) && event.shiftKey && event.key === 'J',
            // Ctrl+W (close tab)
            (event.ctrlKey || event.metaKey) && event.key === 'w',
            // Ctrl+N (new window)
            (event.ctrlKey || event.metaKey) && event.key === 'n',
            // Ctrl+T (new tab)
            (event.ctrlKey || event.metaKey) && event.key === 't',
        ].some(Boolean);

        if (blocked) {
            event.preventDefault();
            event.stopPropagation();
            keyboardViolationCount.value++;

            const keys = [
                event.ctrlKey ? 'Ctrl' : '',
                event.altKey ? 'Alt' : '',
                event.shiftKey ? 'Shift' : '',
                event.metaKey ? 'Cmd' : '',
                event.key,
            ].filter(Boolean).join('+');

            logViolation('keyboard_shortcut', `Blocked shortcut: ${keys}`);

            toast.warning('Keyboard Shortcut Blocked', {
                description: `${keys} is disabled during the activity.`,
            });

            return false;
        }
    };

    // Handle context menu (right-click)
    const handleContextMenu = (event: MouseEvent) => {
        if (!options.enabled) return;

        event.preventDefault();
        logViolation('context_menu', 'Right-click menu attempt blocked');

        toast.warning('Right-click Disabled', {
            description: 'Context menu is disabled during the activity.',
        });
    };

    // Handle fullscreen changes
    const handleFullscreenChange = () => {
        isFullscreen.value = !!document.fullscreenElement;

        if (!isFullscreen.value && options.requireFullscreen && isMonitoring.value) {
            fullscreenExitCount.value++;
            logViolation('fullscreen_exit', 'User exited fullscreen mode');

            toast.warning('Return to Fullscreen', {
                description: 'Please stay in fullscreen mode during the activity.',
            });
        }
    };

    const requestFullscreen = async () => {
        try {
            await document.documentElement.requestFullscreen();
            isFullscreen.value = true;
        } catch (error) {
            console.error('Failed to enter fullscreen:', error);
            toast.error('Fullscreen Required', {
                description: 'Please allow fullscreen mode to continue the activity.',
            });
        }
    };

    // Exit fullscreen
    const exitFullscreen = async () => {
        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
            }
            isFullscreen.value = false;
        } catch (error) {
            console.error('Failed to exit fullscreen:', error);
        }
    };

    // Start monitoring
    const startMonitoring = () => {
        if (isMonitoring.value || !options.enabled) return;

        // Add event listeners
        document.addEventListener('visibilitychange', handleVisibilityChange);
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('contextmenu', handleContextMenu);
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        window.addEventListener('blur', handleWindowBlur);

        // Request fullscreen if required
        if (options.requireFullscreen) {
            requestFullscreen();
        }

        isMonitoring.value = true;
    };

    // Stop monitoring
    const stopMonitoring = () => {
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        document.removeEventListener('keydown', handleKeyDown);
        document.removeEventListener('contextmenu', handleContextMenu);
        document.removeEventListener('fullscreenchange', handleFullscreenChange);
        window.removeEventListener('blur', handleWindowBlur);

        isMonitoring.value = false;
    };

    onMounted(() => {
        if (options.requireFullscreen && options.enabled) {
            setTimeout(() => {
                if (!document.fullscreenElement) {
                    toast.info('Fullscreen Mode', {
                        duration: 8000,
                    });
                    requestFullscreen();
                }
            }, 500);
        }
    });

    // Cleanup
    onBeforeUnmount(() => {
        stopMonitoring();
    });

    return {
        isMonitoring,
        isFullscreen,
        tabSwitchCount,
        fullscreenExitCount,
        keyboardViolationCount,
        startMonitoring,
        stopMonitoring,
        requestFullscreen,
        exitFullscreen,
    };
}