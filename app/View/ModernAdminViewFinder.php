<?php

namespace App\View;

use App\Services\ThemeService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Transparently serves resources/views/admin-modern/* in place of
 * resources/views/admin/* when the current admin's active Design Style
 * is "modern" — without any controller, route, or admin/* file ever
 * being touched. Controllers keep calling view('admin.dashboard.index'),
 * completely unaware this swap is happening.
 *
 * Scoping rules (each one exists to protect a specific real risk):
 *  - Only ever intercepts names under the "admin." view namespace.
 *  - Only swaps for a genuine Admin account (auth()->user()->isAdmin()).
 *    Agents never satisfy this, which matters because a handful of
 *    admin.* blade files (e.g. admin.hotels.room-types.edit) are reused
 *    verbatim by the Agent panel via an explicit $layout override
 *    variable — those must keep rendering the original file for agents
 *    no matter what an agent's own personal design_style is.
 *  - admin.auth.login is a pre-auth special case: no user is logged in
 *    yet, so it falls back to the global (user_id = null) admin theme
 *    instead of a per-user one.
 *  - If no admin-modern/* equivalent exists yet for a given view, this
 *    silently falls back to the classic admin/* file (InvalidArgumentException
 *    from the inner finder is caught) — this is what makes the phased,
 *    module-by-module rollout safe: enabling Modern never breaks a page
 *    that hasn't been ported yet, it just keeps showing Classic for it.
 *  - Any failure resolving the active theme (e.g. mid-request auth state
 *    weirdness) is swallowed and treated as "stay on Classic" — a theme
 *    lookup must never be able to break a page render.
 */
class ModernAdminViewFinder implements ViewFinderInterface
{
    public function __construct(protected ViewFinderInterface $inner)
    {
    }

    public function find($name)
    {
        if ($this->shouldTryModern($name)) {
            $modernName = 'admin-modern.'.substr($name, strlen('admin.'));

            try {
                return $this->inner->find($modernName);
            } catch (InvalidArgumentException) {
                // No Modern equivalent authored yet for this view — fall
                // through and resolve the classic admin.* name below.
            }
        }

        return $this->inner->find($name);
    }

    protected function shouldTryModern(string $name): bool
    {
        if (! str_starts_with($name, 'admin.')) {
            return false;
        }

        // Layout/partial names are never swapped, only page-level views are.
        // Every design's own pages hardcode their own layout tree directly
        // (e.g. admin-modern/dashboard/index.blade.php says @extends
        // ('admin-modern.layouts.app'), never the generic 'admin.layouts.app'
        // name) — so this prefix is only ever hit when an UNCONVERTED
        // Classic page's own @extends('admin.layouts.app') resolves. Swapping
        // it there would wrap still-Classic content in a different design's
        // chrome, which is exactly the bug this guard prevents.
        if (str_starts_with($name, 'admin.layouts.')) {
            return false;
        }

        try {
            /** @var Guard $auth */
            $auth = app('auth')->guard();

            if ($name === 'admin.auth.login') {
                return app(ThemeService::class)->active(null)->isModernDesign();
            }

            $user = $auth->user();

            if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
                return false;
            }

            return app(ThemeService::class)->active($user->id)->isModernDesign();
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
