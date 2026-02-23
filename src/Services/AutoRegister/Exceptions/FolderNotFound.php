<?php
namespace Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Exceptions;

use Exception;

class FolderNotFound extends Exception
{
    /**
     * Exception message.
     * 
     * @var string
     */
    protected $message = "Folder not found for container auto registration.";

    /**
     * Folder path.
     * 
     * @var string
     */
    protected $path;

    /**
     * Setting folder path
     * 
     * @param string $path
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
    
    /**
     * Creating exception for given path.
     * 
     * @param string $path
     * @return self
     */ 
    public static function path(string $path): self
    {
        return (new FolderNotFound)
            ->setPath($path);
    }

    /**
     * Exception context.
     * 
     * @return array
     */
    public function context(): array
    {
        return [
            'path' => $this->path
        ];
    }
}