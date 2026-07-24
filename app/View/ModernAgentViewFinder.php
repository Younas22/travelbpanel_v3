<?php

namespace App\View;

use App\Services\ThemeService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Transparently serves resources/views/agent-modern/* in place of
 * resources/views/agent/* when the current agent's personal Design Style
 * is "modern" — without any controller, route, or agent/* file ever being
 * touched. Controllers keep calling view('agent.dashboard'), completely
 * unaware this swap is happening. Mirrors ModernAdminViewFinder exactly,
 * scoped to the "agent." namespace and gated on User::isAgent() instead
 * of isAdmin() — see that class's docblock for the full rationale behind
 * each scoping rule (identical reasoning applies here).
 *
 * Note: several agent.* views (e.g. agent.hotels.index, agent.tours.index)
 * are actually rendered by Admin\HotelController/TourController/etc via an
 * isAgent() branch in those controllers — but the VIEW NAME passed to
 * view() in that branch is always 'agent.*', never 'admin.*', so this
 * finder only ever needs to watch for the 'agent.' prefix; it never needs
 * to know about that admin-controller-reuse pattern at all.
 */
class ModernAgentViewFinder implements ViewFinderInterface
{
    public function __construct(protected ViewFinderInterface $inner)
    {
    }

    public function find($name)
    {
        if ($this->shouldTryModern($name)) {
            $modernName = 'agent-modern.'.substr($name, strlen('agent.'));

            try {
                return $this->inner->find($modernName);
            } catch (InvalidArgumentException) {
                // No Modern equivalent authored yet for this view — fall
                // through and resolve the classic agent.* name below.
            }
        }

        return $this->inner->find($name);
    }

    protected function shouldTryModern(string $name): bool
    {
        if (! str_starts_with($name, 'agent.')) {
            return false;
        }

        try {
            /** @var Guard $auth */
            $auth = app('auth')->guard();

            if ($name === 'agent.auth.login' || $name === 'agent.auth.register') {
                return app(ThemeService::class)->active(null)->isModernDesign();
            }

            $user = $auth->user();

            if (! $user || ! method_exists($user, 'isAgent') || ! $user->isAgent()) {
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
