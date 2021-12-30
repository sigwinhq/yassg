<?php

namespace Sigwin\YASSG;

class Generator
{
    public function __construct(private string $baseUrl)
    {}
    
    public function generate(Renderer $renderer, string $buildDir): void
    {
        $this->mkdir($buildDir);
        
        foreach ($renderer->permute($this->baseUrl) as $url => $response) {
            fwrite(STDOUT, sprintf('Rendering "%1$s".'."\n", $url ?: '/'));

            $path = $buildDir.$url .'/index.html';
            $this->mkdir(dirname($path));
            
            file_put_contents($path, $response);
            fwrite(STDOUT, sprintf('Wrote %1$s.'."\n", $path));
        }
    }
    
    private function mkdir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
