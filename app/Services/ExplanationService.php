<?php

namespace App\Services;

use App\Models\Detection;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;

class ExplanationService
{
    public function generateExplanation(Detection $detection): string
    {
        $detection->load(['submissionA.user', 'submissionB.user']);

        $prompt = $this->buildPrompt($detection);

        try {
            $response = Prism::text()
                ->using('ollama', 'qwen2.5-coder:7b')
                ->withPrompt($prompt)
                ->withMaxTokens(800)
                ->withClientOptions([
                    'timeout' => 120,
                    'connect_timeout' => 10,
                ])
                ->generate();

            return $response->text;
        } catch (\Exception $e) {
            Log::error("Failed to generate AI explanation: {$e->getMessage()}");
            throw $e;
        }
    }

    private function buildPrompt(Detection $detection): string
    {
        $codeA = $this->extractMatchedCode(
            $detection->submissionA->code_content,
            $detection->line_matches,
            'a'
        );

        $codeB = $this->extractMatchedCode(
            $detection->submissionB->code_content,
            $detection->line_matches,
            'b'
        );

        $lineMatchesFormatted = $this->formatLineMatches($detection->line_matches);

        return <<<PROMPT
You are a code similarity expert analyzing student programming submissions for plagiarism detection.

## Similarity Scores
- Sequential Similarity: {$detection->seq_score}%
- Structural Similarity: {$detection->struct_score}%
- Average Similarity: {$detection->avg_score}%

## Student A's Code (Matched Sections)
```{$detection->submissionA->language}
{$codeA}
```

## Student B's Code (Matched Sections)
```{$detection->submissionB->language}
{$codeB}
```

## Matched Line Ranges
{$lineMatchesFormatted}

## Task
Provide a concise 3-4 sentence explanation of why these submissions are similar. Reference:
1. Specific code patterns or structures that match between the submissions
2. Whether the similarity appears intentional (potential plagiarism) or coincidental (common patterns)
3. The most significant similarities based on the scores and code sections

Keep the explanation educational, objective, and helpful for teachers reviewing potential academic integrity issues.
PROMPT;
    }

    private function extractMatchedCode(string $code, array $lineMatches, string $side): string
    {
        $lines = explode("\n", $code);
        $extractedSections = [];

        foreach ($lineMatches as $match) {
            $start = $side === 'a' ? ($match['a_start'] ?? 0) : ($match['b_start'] ?? 0);
            $end = $side === 'a' ? ($match['a_end'] ?? 0) : ($match['b_end'] ?? 0);

            $contextStart = max(0, $start - 1);
            $contextEnd = min(count($lines) - 1, $end + 1);

            $section = array_slice($lines, $contextStart, $contextEnd - $contextStart + 1);

            $lineNumbers = range($contextStart + 1, $contextEnd + 1);
            $numberedLines = array_map(
                fn ($lineNum, $lineContent) => sprintf('%4d | %s', $lineNum, $lineContent),
                $lineNumbers,
                $section
            );

            $extractedSections[] = implode("\n", $numberedLines);
        }

        if (empty($extractedSections)) {
            return substr(implode("\n", array_slice($lines, 0, 20)), 0, 500) . '...';
        }

        return implode("\n\n---\n\n", $extractedSections);
    }

    private function formatLineMatches(array $lineMatches): string
    {
        if (empty($lineMatches)) {
            return 'No specific line matches recorded.';
        }

        return collect($lineMatches)
            ->map(fn ($match) => sprintf(
                '- Student A lines %d-%d ↔ Student B lines %d-%d',
                $match['a_start'] ?? 0,
                $match['a_end'] ?? 0,
                $match['b_start'] ?? 0,
                $match['b_end'] ?? 0
            ))
            ->join("\n");
    }
}
