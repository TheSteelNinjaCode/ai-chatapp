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
                'take' => 5
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
        description: 'List all available university careers and degrees.'
    )]
    public function getAllCareers(): string
    {
        try {
            // Using findMany() as requested
            // Added 'take' to prevent token limit errors if list is huge
            $careers = $this->prisma->careerOption->findMany([
                'orderBy' => ['career' => 'asc'],
                'take' => 50
            ]);

            if (empty($careers)) {
                return "No careers found in the database.";
            }

            return $this->formatCareers($careers);
        } catch (Throwable $e) {
            return "Database Error: " . $e->getMessage();
        }
    }

    // Helper to keep formatting consistent across both tools
    private function formatCareers(array $careers): string
    {
        $output = "Found " . count($careers) . " options:\n";
        foreach ($careers as $c) {
            // Using object syntax ($c->prop) as per your snippet
            $output .= "- **{$c->career}** ({$c->level})\n";
            $output .= "  Shift: {$c->shift} | Area: {$c->area}\n";
            $output .= "  Desc: " . substr($c->description, 0, 100) . "...\n\n";
        }
        return $output;
    }
}
