<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Testing\TestView;
use Statamic\Tags\Loader;
use Statamic\View\Antlers\Language\Analyzers\NodeTypeAnalyzer;
use Statamic\View\Antlers\Language\Lexer\AntlersLexer;
use Statamic\View\Antlers\Language\Parser\DocumentParser;
use Statamic\View\Antlers\Language\Parser\LanguageParser;
use Statamic\View\Antlers\Language\Runtime\EnvironmentDetails;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\ModifierManager;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Cascade;

trait InteractsWithAntlersViews
{
    /**
     * Render the contents of the given Antlers template string.
     *
     * @param  string  $template
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @return \Illuminate\Testing\TestView
     */
    protected function antlers(string $template, $data = []): TestView
    {
        $tempDirectory = sys_get_temp_dir();

        if (! in_array($tempDirectory, ViewFacade::getFinder()->getPaths())) {
            ViewFacade::addLocation(sys_get_temp_dir());
        }

        $tempFileInfo = pathinfo(tempnam($tempDirectory, 'laravel-antlers'));

        $tempFile = $tempFileInfo['dirname'].'/'.$tempFileInfo['filename'].'.antlers.html';

        file_put_contents($tempFile, $template);

        return new TestView(view($tempFileInfo['filename'], $data));
    }
}
