<?php

declare(strict_types=1);

namespace app\_components;

use Lib\PPIcons\{Moon, SunMoon};

use PP\PHPX\PHPX;
use PP\Request;

class ToggleTheme extends PHPX
{
    public ?string $class = '';
    private string $themeName = '';

    public function __construct(array $props = [])
    {
        parent::__construct($props);

        $this->themeName = Request::$localStorage->themeName ?? 'dark';
    }

    public function render(): string
    {
        $class = $this->getMergeClasses($this->class);
        $attributes = $this->getAttributes([
            'class' => $class,
        ]);
        $isDark = $this->themeName === 'dark' ? 'true' : 'false';

        return <<<HTML
        <div {$attributes}>
            <button class="p-2 text-muted-foreground hover:text-foreground transition-colors" onclick="toggleThemeName()">
                <SunMoon class="size-5" hidden="{toggleTheme}" />
                <Moon class="size-5" hidden="{!toggleTheme}" />
            </button>

            <script>
                const [toggleTheme, setToggleTheme] = pp.state({$isDark});

                function toggleThemeName() {
                    setToggleTheme(!toggleTheme);
                    const newThemeName = toggleTheme ? 'dark' : 'light';
                    document.documentElement.setAttribute('class', newThemeName);
                    store.setState({
                        themeName: newThemeName
                    }, true);
                }
            </script>
        </div>
        HTML;
    }
}
