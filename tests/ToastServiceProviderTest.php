<?php declare(strict_types=1);

namespace Tests;

use Dive\Crowbar\Crowbar;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Arr;
use MAS\Toast\Collector;
use MAS\Toast\LivewireRelay;
use MAS\Toast\SessionRelay;
use MAS\Toast\ToastHub;
use MAS\Toast\TranslatingCollector;
use MAS\Toast\ToastServiceProvider;

final class ToastServiceProviderTest extends TestCase
{
    /** @test */
    public function it_binds_the_service_as_a_singleton(): void
    {
        $this->assertTrue($this->app->isShared(Collector::class));
        $this->assertTrue($this->app->isAlias(ToastServiceProvider::NAME));
        $this->assertInstanceOf(TranslatingCollector::class, $this->app[ToastServiceProvider::NAME]);
    }

    /** @test */
    public function it_registers_the_relays_only_after_the_service_has_been_resolved_at_least_once(): void
    {
        $events = Crowbar::pry($this->app['events']);
        $livewire = Crowbar::pry($this->app['livewire']);

        $this->assertNotContains(SessionRelay::class, $events->listeners[RequestHandled::class] ?? []);
        $this->assertNotContains(LivewireRelay::class, $livewire->listeners['component.dehydrate']);

        $this->app[ToastServiceProvider::NAME];

        $this->assertContains(SessionRelay::class, $events->listeners[RequestHandled::class]);
        $this->assertInstanceOf(LivewireRelay::class, Arr::last($livewire->listeners['component.dehydrate']));
    }

    /** @test */
    public function it_registers_the_toast_hub_as_a_blade_component(): void
    {
        $blade = Crowbar::pry($this->app['blade.compiler']);

        $this->assertArrayHasKey('toast-hub', $blade->classComponentAliases);
        $this->assertSame(ToastHub::class, $blade->classComponentAliases['toast-hub']);
    }


}