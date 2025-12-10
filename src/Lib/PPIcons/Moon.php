<?php

namespace Lib\PPIcons;

use PP\PHPX\PHPX;

class Moon extends PHPX
{
    public ?string $class = '';

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function render(): string
    {
        $class = $this->getMergeClasses($this->class);
        $attributes = $this->getAttributes([
            'class' => $class,
        ]);

        return <<<HTML
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" {$attributes}><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
        HTML;
    }
}
