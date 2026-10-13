<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class SmartySecurityTest extends TestCase
{
    private \Smarty $smarty;

    protected function setUp(): void
    {
        $this->smarty = new \Smarty();
        $this->smarty->setTemplateDir(dirname(__DIR__, 2) . '/templates');
        $this->smarty->setCompileDir(sys_get_temp_dir() . '/smarty_test_compiled');
        $this->smarty->setCacheDir(sys_get_temp_dir() . '/smarty_test_cache');
        $this->smarty->escape_html = true;

        if (!is_dir(sys_get_temp_dir() . '/smarty_test_compiled')) {
            mkdir(sys_get_temp_dir() . '/smarty_test_compiled', 0775, true);
        }
    }

    public function testXssEscapedInVariableOutput(): void
    {
        $this->smarty->assign('testVar', '<script>alert("xss")</script>');

        $output = $this->smarty->fetch('string:{$testVar}');

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testContentNl2brPreservesBrTags(): void
    {
        $this->smarty->assign('testContent', "Строка 1\nСтрока 2 <b>жирный</b>");

        $output = $this->smarty->fetch(
            "string:{\$testContent|escape:'html'|nl2br nofilter}"
        );

        $this->assertStringContainsString('&lt;b&gt;', $output);
        $this->assertStringContainsString('<br', $output);
        $this->assertStringNotContainsString('<b>', $output);
    }
}