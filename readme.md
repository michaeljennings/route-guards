# Route Guards

## Installation

To install the package run

```
composer require michaeljennings/route-guards
```

Once the package is installed, add the `MichaelJennings\RouteGuards\GuardRoutes` middleware to you `App\Http\Kernel.php`, 
if you are using model bindings makes ure to register it after the `SubstituteBinding` middleware.

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \MichaelJennings\RouteGuards\GuardRoutes::class,
    ],
];
```

## Usage

### Registering Guards

You can guard a route in 3 different ways.

Firstly, you can chain a guard method on the route.

```php
Route::get('/products', 'ProductController@index')->guard(ProductGuard::class);
```

Or you can set the guard in the route action.

```php
Route::get('/products', ['uses' => 'ProductController@index', 'guard' => ProductGuard::class]);
```

Finally, you can specify the guard in a route group.

```php
Route::group(['prefix' => 'products', 'guard' => ProductGuard::class], function () {
    Route::get('/', 'ProductController@index');
});
```

### Writing Guards

By default we will try to hit an authorize method on the route guard. The authorize method should return true if the 
user is allowed to access the route, and false if they aren't.

```php
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class ProductGuard extends RouteGuard
{
    public function authorize(Route $route): bool
    {
        return Auth::user()->hasPermission('products.read');    
    }
}
```

Occasionally you might want to authorize one endpoint in a group differently to the others. For example, in the below 
routes we want to check for a different to view the products and to create a product
 
```php
Route::group(['prefix' => 'products', 'guard' => ProductGuard::class], function () {
    Route::get('/', 'ProductController@index');
    Route::post('/', 'ProductController@store');
});
```

To do this simply add a method with the same name as the controller method to your route guard.

```php
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class ProductGuard extends RouteGuard
{
    public function index(Route $route): bool
    {
        return Auth::user()->hasPermission('products.read');    
    }

    public function store(Route $route): bool
    {
        return Auth::user()->hasPermission('products.create');    
    }
}
```

### Model Bindings

For certain routes you may also want to check that a user can access a specific record, for example in the route below
we want to make sure the user can access a record before they can make updates to it.

```php
Route::put('/products/{product}', 'ProductController@update')->guard(ProductGuard::class);
``` 

By default we will attempt to use model bindings to access the model from the route and pass it into the authorize 
method.

```php
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class ProductGuard extends RouteGuard
{
    // This works for authorize
    public function authorize(Route $route, Model $model): bool
    {
        // Check the user can access the model 
    }

    // It also works for the custom methods
    public function update(Route $route, Model $model): bool
    {
        // Check the user can access the model
    }
}
```

### Custom Bindings

If you aren't using model bindings but still want to take advantage of finding your resource in the route guard you
can override the `find` method.

```php
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class ProductGuard extends RouteGuard
{
    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    // This works for authorize
    public function authorize(Route $route, Model $model): bool
    {
        // Check the user can access the model 
    }

    protected function find(Route $route, string $binding)
    {
        return $this->productRepository->find(
            $route->parameter($binding)
        );
    }
}
```

### Using Multiple Guards

In the route below we have to parameters in one route that need to be guarded differently.

```php
Route::get('/products/{product}/variants/{variant}', 'Product\VariantController@show');
```

We can do this by setting two guards on the route, and telling them which parameter they guard.

```php
Route::get('/products/{product}/variants/{variant}', 'Product\VariantController@show')
     ->guard(ProductGuard::class, 'product')
     ->guard(VariantGuard::class, 'variant');
```

You can also define this on a route group by providing an array where the key is the parameter the guard will protect.

```php
Route::group(['prefix' => 'products/{product}/variants/{variant}', 'guards' => [
    'product' => ProductGuard::class, 
    'variant' => Variant::guard
]], function () {
    Route::get('/', 'Product\VariantController@show');
});
```

### Customising Exceptions

If authorization fails we will throw the `Illuminate\Auth\Access\AuthorizationException` exception. If you want to
change this you can override the `authorizationFailed` method. 
 
```php
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class ProductGuard extends RouteGuard
{
    public function authorize(Route $route): bool
    {
        return false; 
    }

    protected function authorizationFailed(): void
    {
        throw new CustomException();
    }
}
```

Occasionally you might want to throw a different exception for your index endpoint than your create endpoint. You can 
this by taking the name of the method and adding failed to it.

```php
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class ProductGuard extends RouteGuard
{
    public function index(Route $route): bool
    {
        return false; 
    }

    protected function indexFailed(): void
    {
        throw new IndexException();
    }


    public function create(Route $route): bool
    {
        return false; 
    }

    protected function createFailed(): void
    {
        throw new CreateException();
    }
}
```
