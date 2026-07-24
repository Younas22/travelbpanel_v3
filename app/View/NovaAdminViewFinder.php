<?php

namespace App\View;

use App\Services\ThemeService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Transparently serves resources/views/admin-nova/* in place of
 * resources/views/admin/* when the active Design Style is "nova" — mirrors
 * ModernAdminViewFinder exactly (same scoping rules, same gate on
 * User::isAdmin()), just for the third design and its own view prefix.
 *
 * Nova is being rolled out phased, module-by-module, same as Modern was —
 * right now only admin-nova/dashboard/index.blade.php (plus its own
 * layouts/app.blade.php shell) exists. Any admin.* page with no admin-nova.*
 * equivalent yet falls through this finder to the next one in the chain
 * (ModernAdminViewFinder), which itself only swaps for design_style ===
 * "modern" — so for a Nova user that inner check is false too, and it falls
 * all the way through to the base finder, i.e. Classic. That fallback to
 * Classic (not Modern) for not-yet-built Nova pages is intentional: Nova and
 * Modern are independent, mutually exclusive designs, not a fallback chain
 * of their own.
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

        // Layout/partial names are never swapped, only page-level views are
        // — see ModernAdminViewFinder::shouldTryModern() for the full
        // rationale (same bug class, same fix, both designs hardcode their
        // own layout tree directly and never rely on this swap for it).
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
