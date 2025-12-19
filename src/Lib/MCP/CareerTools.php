<?php

declare(strict_types=1);

namespace Lib\MCP;

use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;
use Lib\Prisma\Classes\Prisma;
use Throwable;

final class CareerTools
{
    private Prisma $prisma;

    public function __construct()
    {
        $this->prisma = Prisma::getInstance();
    }

    #[McpTool(
        name: 'search-careers',
        description: 'Search for university careers/degrees based on keywords, area, or shift.'
    )]
    public function searchCareers(
        #[Schema(type: 'string', description: 'Search keyword (e.g., "Engineering", "Night Shift", "Health")')]
        string $query
    ): string {
        try {
            $careers = $this->prisma->careerOption->findMany([
                'where' => [
                    'OR' => [
                        ['career' => ['contains' => $query]],
                        ['area'   => ['contains' => $query]],
                        ['description' => ['contains' => $query]],
                        ['shift' => ['contains' => $query]],
                    ]
                ],
                'take' => 10 // Increased from 5 to gives better context
            ]);

            if (empty($careers)) {
                return "No career options found matching: " . $query;
            }

            return $this->formatCareers($careers);
        } catch (Throwable $e) {
            return "Database Error: " . $e->getMessage();
        }
    }

    #[McpTool(
        name: 'get-all-careers',
        description: 'Get a summary count and list of all available university careers.'
    )]
    public function getAllCareers(): string
    {
        try {
            // 1. Get the REAL total count from the database
            $totalCount = $this->prisma->careerOption->count();

            // 2. Fetch the list (limit to 50 to avoid crashing the AI context)
            $careers = $this->prisma->careerOption->findMany([
                'orderBy' => ['career' => 'asc'],
                'take' => 50
            ]);

            if (empty($careers)) {
                return "No careers found in the database.";
            }

            // 3. Explicitly tell the AI the total count
            $output = "DATABASE REPORT: There are exactly **{$totalCount}** careers in the database.\n";

            // 4. Append the list
            $output .= $this->formatCareers($careers);

            return $output;
        } catch (Throwable $e) {
            return "Database Error: " . $e->getMessage();
        }
    }

    private function formatCareers(array $careers): string
    {
        $output = "List of fetched results:\n";
        foreach ($careers as $c) {
            $output .= "- **{$c->career}** ({$c->level})\n";
            $output .= "  Shift: {$c->shift} | Area: {$c->area}\n";
            // Shortened description to save tokens
            $output .= "  Desc: " . substr((string)$c->description, 0, 80) . "...\n\n";
        }
        return $output;
    }
}
