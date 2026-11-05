<?php

namespace App\View;

use App\Services\ThemeService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Transparently serves resources/views/admin-nova/* in place of
 * resources/views/admin/* when the active Design Style is "nova" (only for
 * logged-in admins, User::isAdmin()).
 *
 * Nova is rolled out page by page: any admin.* page with no admin-nova.*
 * equivalent yet falls through to the base finder, i.e. Classic.
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

        try {
            /** @var Guard $auth */
            $auth = app('auth')->guard();

            if ($name === 'admin.auth.login') {
                return app(ThemeService::class)->active(null)->design_style === 'nova';
            }

            $user = $auth->user();

            if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
                return false;
            }

            return app(ThemeService::class)->active($user->id)->design_style === 'nova';
        } catch (Throwable) {
            return false;
        }
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
