<?php

namespace app\http\attribute;

use Attribute;
use Cclilshy\PRipple\Http\Service\Request;
use Component\LaravelComponent;
use Core\Container\Container;
use Core\Container\Exception\Exception;
use Core\Standard\AttributeInterface;
use Illuminate\Validation\Validator;
use Override;
use PRipple\Framework\Interface\ValidatorInterface;
use PRipple\Framework\Route\Route;
use Throwable;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
class Validate implements AttributeInterface
{
    private ValidatorInterface $validatorConfig;
    public Validator           $validator;

    /**
     * @param string $validateClass
     */
    public function __construct(string $validateClass)
    {
        $this->validatorConfig = new $validateClass;
    }

    /**
     * @param Container $container
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    #[Override] public function buildAttribute(Container $container): void
    {
        $request    = $container->make(Request::class);
        $route      = $request->make(Route::class);
        $methods    = $this->validatorConfig->method();
        $accordWith = match (is_string($methods)) {
            true => $methods === $route->requestMethod(),
            false => in_array($route->requestMethod(), $methods)
        };
        if ($accordWith) {
            $this->validator = new Validator(
                LaravelComponent::$laravel->translator,
                array_merge($request->query(), $request->post()),
                $this->validatorConfig->rules(),
                $this->validatorConfig->messages(),
            );
        }
    }
}
