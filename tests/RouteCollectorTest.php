<?php
namespace ngyuki\RouteCollector\Tests;

use PHPUnit\Framework\TestCase;
use ngyuki\RouteCollector\RouteRegistry;

class RouteCollectorTest extends TestCase
{
    function collect(callable $callback)
    {
        $registry = new RouteRegistry();
        $callback(new ActionRouteCollector($registry));
        return $registry->getRoutes();
    }

    /**
     * @test
     */
    function example_()
    {
        $registry = new RouteRegistry();
        $r = new ActionRouteCollector($registry);

        // GET / -> HomeController::index
        $r->path('/')->get()->controller('HomeController')->action('index');

        // GET|POST /both -> HomeController::both
        $r->path('/both')->get()->post()->controller('HomeController')->action('both');

        // GET|POST /arr -> HomeController::arr
        $r->path('/method')->method('get|POST')->controller('HomeController')->action('method');

        $r->controller('UserController')->group(function (ActionRouteCollector $r) {
            $r->path('/user')->group(function (ActionRouteCollector $r) {

                // GET /user -> UserController::index
                $r->get()->action('index');

                // GET /user/create -> UserController::create
                $r->path('/create')->get()->action('create');

                // POST /user/create -> UserController::store
                $r->path('/create')->post()->action('store');
            });
            $r->path('/user/{id}')->group(function (ActionRouteCollector $r) {

                // GET /user/{id} -> UserController::show
                $r->get()->action('show');

                // GET /user/{id}/edit -> UserController::edit
                $r->path('/edit')->get()->action('edit');

                // PUT /user/{id}/edit -> UserController::update
                $r->path('/edit')->put()->action('update');

                // DELETE /user/{id}/edit -> UserController::delete
                $r->path('/edit')->delete()->action('delete');
            });
        });

        $expected = [
            [['GET'], '/', ['controller' => 'HomeController', 'action' => 'index']],
            [['GET', 'POST'], '/both', ['controller' => 'HomeController', 'action' => 'both']],
            [['GET', 'POST'], '/method', ['controller' => 'HomeController', 'action' => 'method']],
            [['GET'], '/user', ['controller' => 'UserController', 'action' => 'index']],
            [['GET'], '/user/create', ['controller' => 'UserController', 'action' => 'create']],
            [['POST'], '/user/create', ['controller' => 'UserController', 'action' => 'store']],
            [['GET'], '/user/{id}', ['controller' => 'UserController', 'action' => 'show']],
            [['GET'], '/user/{id}/edit', ['controller' => 'UserController', 'action' => 'edit']],
            [['PUT'], '/user/{id}/edit', ['controller' => 'UserController', 'action' => 'update']],
            [['DELETE'], '/user/{id}/edit', ['controller' => 'UserController', 'action' => 'delete']],
        ];

        self::assertEquals($expected, $registry->getRoutes());
    }

    /**
     * @test
     */
    function empty_()
    {
        $routes = $this->collect(function(ActionRouteCollector $r) {});

        self::assertEquals([], $routes);
    }

    /**
     * @test
     */
    function empty_group()
    {
        $routes = $this->collect(function(ActionRouteCollector $r) {
            $r->group(function(){});
        });

        self::assertEquals([], $routes);
    }

    /**
     * @test
     */
    function empty_group_with_route()
    {
        $routes = $this->collect(function(ActionRouteCollector $r) {
            $r->get()->path('/')->controller('HomeController')->action('index')->group(function(){});
        });

        self::assertEquals([], $routes);
    }
}
