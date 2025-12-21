interface PistonExecuteRequest {
    language: string;
    version: string;
    files: Array<{
        name?: string;
        content: string;
    }>;
    stdin?: string;
    args?: string[];
    compile_timeout?: number;
    run_timeout?: number;
    compile_memory_limit?: number;
    run_memory_limit?: number;
}

interface PistonExecuteResponse {
    language: string;
    version: string;
    run: {
        stdout: string;
        stderr: string;
        code: number;
        signal: string | null;
        output: string;
    };
    compile?: {
        stdout: string;
        stderr: string;
        code: number;
        signal: string | null;
        output: string;
    };
}

interface PistonRuntime {
    language: string;
    version: string;
    aliases: string[];
}

import axios from 'axios';

// Use shared API endpoint accessible to both students and teachers
const API_BASE_URL = '/api/code';

// Map Laravel language enum to Piston language identifiers
const LANGUAGE_MAP: Record<string, { language: string; version: string }> = {
    'java': { language: 'java', version: '15.0.2' },
    'python': { language: 'python', version: '3.12.0' },
};

export class PistonService {
    /**
     * Execute code using Piston API
     */
    static async executeCode(
        language: string,
        code: string,
        stdin: string = ''
    ): Promise<PistonExecuteResponse> {
        const languageConfig = LANGUAGE_MAP[language];

        if (!languageConfig) {
            throw new Error(`Unsupported language: ${language}`);
        }

        // Auto-wrap Java code if it's just a main method without a class
        let processedCode = code;
        if (language === 'java') {
            // Check if code doesn't have a class declaration
            if (!code.trim().match(/^(public\s+)?class\s+\w+/m)) {
                // Wrap the code in a Main class
                processedCode = `public class Main {\n${code}\n}`;
            }
        }

        const request: PistonExecuteRequest = {
            language: languageConfig.language,
            version: languageConfig.version,
            files: [
                {
                    content: processedCode,
                },
            ],
            stdin,
            compile_timeout: 10000,
            run_timeout: 3000,
            compile_memory_limit: -1,
            run_memory_limit: -1,
        };

        try {
            const response = await axios.post(`${API_BASE_URL}/execute`, request);
            return response.data;
        } catch (error: any) {
            const errorMessage = error.response?.data?.message || error.response?.data?.error || error.message;
            throw new Error(`Code execution error: ${errorMessage}`);
        }
    }

    /**
     * Get available runtimes from Piston API
     */
    static async getRuntimes(): Promise<PistonRuntime[]> {
        try {
            const response = await axios.get(`${API_BASE_URL}/runtimes`);
            return response.data;
        } catch (error: any) {
            const errorMessage = error.response?.data?.message || error.response?.data?.error || error.message;
            throw new Error(`Code execution error: ${errorMessage}`);
        }
    }

    /**
     * Format execution output for display
     */
    static formatOutput(result: PistonExecuteResponse, stdin?: string): string {
        let output = '';

        // Add compile output if present (for compiled languages like Java)
        if (result.compile) {
            if (result.compile.stdout) {
                output += `=== Compilation Output ===\n${result.compile.stdout}\n\n`;
            }
            if (result.compile.stderr) {
                output += `=== Compilation Errors ===\n${result.compile.stderr}\n\n`;
            }
            if (result.compile.code !== 0) {
                output += `Compilation failed with exit code ${result.compile.code}\n\n`;
                return output;
            }
        }

        // Add run output
        if (result.run.stdout) {
            let cleanedOutput = result.run.stdout;

            // Python input() prompts appear concatenated with stdin values
            // We need to properly format them to look like IDE output
            if (stdin && stdin.trim()) {
                const stdinLines = stdin.trim().split('\n');
                let stdinIndex = 0;

                // Replace each stdin value that appears right after a prompt with prompt + value on same line
                cleanedOutput = cleanedOutput.replace(/([^\n]*[?:>]\s*)(\S[^\n]*?)(?=\n|$)/g, (match, prompt, restOfLine) => {
                    // Check if this looks like an input prompt (ends with ?, :, or >)
                    const isPrompt = /[?:>]\s*$/.test(prompt);

                    if (isPrompt && stdinIndex < stdinLines.length) {
                        const stdinValue = stdinLines[stdinIndex];
                        // Check if the stdin value appears at the start of restOfLine
                        if (restOfLine.startsWith(stdinValue)) {
                            stdinIndex++;
                            // Keep prompt and stdin on same line, move rest to new line
                            const afterStdin = restOfLine.substring(stdinValue.length);
                            return prompt + stdinValue + (afterStdin ? '\n' + afterStdin.trimStart() : '');
                        }
                    }
                    return match;
                });
            }

            output += `=== Program Output ===\n${cleanedOutput}\n`;
        }

        if (result.run.stderr) {
            output += `\n=== Errors ===\n${result.run.stderr}\n`;
        }

        if (result.run.code !== 0) {
            output += `\nProgram exited with code ${result.run.code}`;
        }

        if (!result.run.stdout && !result.run.stderr) {
            output = 'Program executed successfully with no output.';
        }

        return output || 'No output';
    }
}
