<?php

namespace App\Services\Lesson;

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentProcessor
{
    /**
     * Extract text from uploaded file
     */
    public function extractText(string $filePath, string $fileType): string
    {
        // Download from S3 to temp location
        $tempPath = $this->downloadToTemp($filePath);

        try {
            $text = match(strtolower($fileType)) {
                'pdf' => $this->extractFromPdf($tempPath),
                'docx', 'doc' => $this->extractFromWord($tempPath),
                'txt' => file_get_contents($tempPath),
                default => throw new \Exception("Unsupported file type: {$fileType}")
            };

            // Clean up temp file
            unlink($tempPath);

            return $this->cleanText($text);

        } catch (\Exception $e) {
            // Clean up temp file on error
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Extract text from PDF
     */
    protected function extractFromPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    /**
     * Extract text from Word document
     */
    protected function extractFromWord(string $path): string
    {
        $phpWord = WordIOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . "\n";
                        }
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Break content into logical segments
     */
    public function breakIntoSegments(string $content, int $maxWordsPerSegment = 300): array
    {
        // Split by double newlines (paragraphs)
        $paragraphs = preg_split('/\n\s*\n/', $content);
        $segments = [];
        $currentSegment = [
            'title' => '',
            'content' => '',
            'word_count' => 0,
        ];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;

            $wordCount = str_word_count($paragraph);

            // Check if this looks like a heading (short, possibly numbered)
            $isHeading = $wordCount < 10 && (
                preg_match('/^(Chapter|Section|Part|\d+\.|\d+\))/i', $paragraph) ||
                preg_match('/^[A-Z][^.!?]*$/', $paragraph)
            );

            if ($isHeading && !empty($currentSegment['content'])) {
                // Save current segment and start new one
                $segments[] = $currentSegment;
                $currentSegment = [
                    'title' => $paragraph,
                    'content' => '',
                    'word_count' => 0,
                ];
            } elseif ($isHeading) {
                // Set as title for current segment
                $currentSegment['title'] = $paragraph;
            } else {
                // Add to current segment
                if ($currentSegment['word_count'] + $wordCount > $maxWordsPerSegment && !empty($currentSegment['content'])) {
                    // Segment is full, save it
                    $segments[] = $currentSegment;
                    $currentSegment = [
                        'title' => 'Continued...',
                        'content' => $paragraph,
                        'word_count' => $wordCount,
                    ];
                } else {
                    $currentSegment['content'] .= $paragraph . "\n\n";
                    $currentSegment['word_count'] += $wordCount;
                }
            }
        }

        // Add last segment
        if (!empty($currentSegment['content'])) {
            $segments[] = $currentSegment;
        }

        // Ensure all segments have titles
        foreach ($segments as $index => &$segment) {
            if (empty($segment['title'])) {
                $segment['title'] = 'Section ' . ($index + 1);
            }
        }

        return $segments;
    }

    /**
     * Clean extracted text
     */
    protected function cleanText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        // Remove excessive newlines (more than 2)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Download file from S3 to temp location
     */
    protected function downloadToTemp(string $s3Path): string
    {
        $tempPath = sys_get_temp_dir() . '/' . Str::uuid() . '_' . basename($s3Path);
        $content = Storage::disk('s3')->get($s3Path);
        file_put_contents($tempPath, $content);
        return $tempPath;
    }

    /**
     * Get metadata from file
     */
    public function getMetadata(string $filePath, string $fileType): array
    {
        $tempPath = $this->downloadToTemp($filePath);

        try {
            $metadata = [
                'file_size' => filesize($tempPath),
                'file_type' => $fileType,
            ];

            if ($fileType === 'pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($tempPath);
                $metadata['page_count'] = count($pdf->getPages());
            }

            unlink($tempPath);
            return $metadata;

        } catch (\Exception $e) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            return ['error' => $e->getMessage()];
        }
    }
}
