<?php

namespace App\View;

use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;

/**
 * Nova is the only admin design: serves resources/views/admin-nova/* in
 * place of resources/views/admin/* whenever a Nova version of the view
 * exists. Pages with no admin-nova.* version yet still render from
 * resources/views/admin/* (through admin.layouts.app, styled by
 * admin-nova.css).
 */
class NovaAdminViewFinder implements ViewFinderInterface
{
    public function __construct(protected ViewFinderInterface $inner)
    {
    }

    public function find($name)
    {
        if ($this->shouldTryNova($name)) {
            $novaName = 'admin-nova.'.substr($name, strlen('admin.'));

            try {
                return $this->inner->find($novaName);
            } catch (InvalidArgumentException) {
                // No Nova equivalent authored yet for this view — fall
                // through and resolve via the next finder in the chain.
            }
        }

        return $this->inner->find($name);
    }

    protected function shouldTryNova(string $name): bool
    {
        if (! str_starts_with($name, 'admin.')) {
            return false;
        }

        // Layout/partial names are never swapped, only page-level views are.
        // Nova's own pages extend 'admin-nova.layouts.app' directly, so this
        // prefix is only hit when an unconverted Classic page's
        // @extends('admin.layouts.app') resolves — swapping it there would
        // wrap Classic content in Nova's chrome.
        if (str_starts_with($name, 'admin.layouts.')) {
            return false;
        }

        return true;
    }

    // -- Pass-through: everything below is unrelated to the swap itself,
    //    ViewFinderInterface just requires these to be implemented. --

    public function addLocation($location)
    {
        $this->inner->addLocation($location);
    }

    public function addNamespace($namespace, $hints)
    {
        $this->inner->addNamespace($namespace, $hints);
    }

    public function prependNamespace($namespace, $hints)
    {
        $this->inner->prependNamespace($namespace, $hints);
    }

    public function replaceNamespace($namespace, $hints)
    {
        $this->inner->replaceNamespace($namespace, $hints);
    }

    public function addExtension($extension)
    {
        $this->inner->addExtension($extension);
    }

    public function exists($name)
    {
        try {
            $this->find($name);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function setChunkInfo($name, $value = null)
    {
        if (method_exists($this->inner, 'setChunkInfo')) {
            $this->inner->setChunkInfo($name, $value);
        }
    }

    public function flush()
    {
        $this->inner->flush();
    }

    public function __call($method, $parameters)
    {
        return $this->inner->{$method}(...$parameters);
    }
}
