<?php

/**
 * Coverage utility
 * Parses Clover XML files and provides total/changed coverage metrics
 */

class CoverageAnalyzer {
    private SimpleXMLElement $xml;
    private array $files = [];

    public function __construct(string $cloverXmlPath) {
        if (!file_exists($cloverXmlPath)) {
            throw new Exception("Clover XML not found: $cloverXmlPath");
        }
        $this->xml = simplexml_load_file($cloverXmlPath);
        if ($this->xml === false) {
            throw new Exception("Unable to parse Clover XML: $cloverXmlPath");
        }
        $this->parseFiles();
    }

    private function parseFiles(): void {
        foreach ($this->xml->xpath('//file') as $file) {
            $filename = (string)$file['name'];
            $metrics = $file->xpath('metrics')[0] ?? null;
            if ($metrics) {
                $this->files[$filename] = [
                    'statements' => (int)$metrics['statements'],
                    'coveredstatements' => (int)$metrics['coveredstatements'],
                    'methods' => (int)$metrics['methods'],
                    'coveredmethods' => (int)$metrics['coveredmethods'],
                ];
            }
        }
    }

    public function getTotalMetrics(): array {
        $metrics = $this->xml->xpath('/coverage/project/metrics')[0] ?? null;
        if (!$metrics) {
            throw new Exception("Unable to find project metrics in Clover XML");
        }

        $totalStatements = (int)$metrics['statements'];
        $coveredStatements = (int)$metrics['coveredstatements'];
        $totalMethods = (int)$metrics['methods'];
        $coveredMethods = (int)$metrics['coveredmethods'];

        return [
            'line_coverage' => $totalStatements > 0 ? round(($coveredStatements / $totalStatements) * 100, 2) : 0.0,
            'method_coverage' => $totalMethods > 0 ? round(($coveredMethods / $totalMethods) * 100, 2) : 0.0,
            'covered_lines' => $coveredStatements,
            'total_lines' => $totalStatements,
            'covered_methods' => $coveredMethods,
            'total_methods' => $totalMethods,
        ];
    }

    public function getMetricsForFiles(array $filenames): array {
        $totalStatements = 0;
        $coveredStatements = 0;
        $totalMethods = 0;
        $coveredMethods = 0;

        foreach ($filenames as $filename) {
            $file = $this->findFileMetrics($filename);
            if ($file !== null) {
                $totalStatements += $file['statements'];
                $coveredStatements += $file['coveredstatements'];
                $totalMethods += $file['methods'];
                $coveredMethods += $file['coveredmethods'];
            }
        }

        return [
            'line_coverage' => $totalStatements > 0 ? round(($coveredStatements / $totalStatements) * 100, 2) : 0.0,
            'method_coverage' => $totalMethods > 0 ? round(($coveredMethods / $totalMethods) * 100, 2) : 0.0,
            'covered_lines' => $coveredStatements,
            'total_lines' => $totalStatements,
            'covered_methods' => $coveredMethods,
            'total_methods' => $totalMethods,
        ];
    }

    private function findFileMetrics(string $filename): ?array {
        if (isset($this->files[$filename])) {
            return $this->files[$filename];
        }

        foreach ($this->files as $cloverPath => $metrics) {
            if (str_ends_with($cloverPath, '/' . $filename) || str_ends_with($cloverPath, $filename)) {
                return $metrics;
            }
        }

        return null;
    }

    public static function getChangedFiles(string $baseBranch = 'master'): array {
        $cmd = "git diff {$baseBranch}...HEAD --name-only";
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Failed to get changed files: git diff failed");
        }

        $changedFiles = [];
        foreach ($output as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $changedFiles[] = $line;
            }
        }
        return $changedFiles;
    }

}

// Handle command-line arguments
$command = $argv[1] ?? null;

try {
    match ($command) {
        'total' => handleTotal($argv),
        'changed' => handleChanged($argv),
        null => fwrite(STDERR, "Usage: php coverage-compare.php <command> [args]\n"),
        default => fwrite(STDERR, "Unknown command: $command\n"),
    };
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}

function handleTotal(array $argv): void {
    if (!isset($argv[2])) {
        throw new Exception("Usage: php coverage-compare.php total <clover-xml>");
    }
    
    $analyzer = new CoverageAnalyzer($argv[2]);
    $metrics = $analyzer->getTotalMetrics();
    
    foreach ($metrics as $key => $value) {
        echo "$key=$value\n";
    }
}

function handleChanged(array $argv): void {
    if (!isset($argv[2]) || !isset($argv[3])) {
        throw new Exception("Usage: php coverage-compare.php changed <clover-xml> <base-branch>");
    }
    
    $analyzer = new CoverageAnalyzer($argv[2]);
    $baseBranch = $argv[3];
    
    $changedFiles = CoverageAnalyzer::getChangedFiles($baseBranch);
    if (empty($changedFiles)) {
        echo "line_coverage=0\n";
        echo "method_coverage=0\n";
        echo "covered_lines=0\n";
        echo "total_lines=0\n";
        echo "covered_methods=0\n";
        echo "total_methods=0\n";
        return;
    }
    
    $metrics = $analyzer->getMetricsForFiles($changedFiles);
    
    foreach ($metrics as $key => $value) {
        echo "$key=$value\n";
    }
}
