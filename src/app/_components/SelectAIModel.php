<?php

declare(strict_types=1);

namespace app\_components;

use Lib\PHPXUI\{Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue};

use PP\PHPX\PHPX;

class SelectAIModel extends PHPX
{
    public ?string $class = '';

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $class = $this->getMergeClasses("flex items-center gap-2 text-sm font-medium text-muted-foreground", $this->class);
        $attributes = $this->getAttributes([
            'class' => $class,
        ]);

        return <<<HTML
        <div {$attributes}>
            <Select>
                <SelectTrigger class="w-[180px] border-none bg-transparent dark:bg-transparent">
                    <SelectValue placeholder="GPT-4" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Modelos</SelectLabel>
                        <SelectItem value="gpt-4">GPT-4</SelectItem>
                        <SelectItem value="gpt-4o">GPT-4o</SelectItem>
                        <SelectItem value="gpt-4o-mini">GPT-4o-mini</SelectItem>
                        <SelectItem value="gpt-4-0613">GPT-4 (0613)</SelectItem>
                        <SelectItem value="gpt-4-32k">GPT-4 32k</SelectItem>
                        <SelectItem value="gpt-3.5-turbo">GPT-3.5 Turbo</SelectItem>
                        <SelectItem value="gpt-3.5-turbo-16k">GPT-3.5 Turbo 16k</SelectItem>
                        <SelectItem value="text-embedding-3-large">text-embedding-3-large (Embeddings)</SelectItem>
                        <SelectItem value="text-embedding-3-small">text-embedding-3-small (Embeddings)</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
        HTML;
    }
}
