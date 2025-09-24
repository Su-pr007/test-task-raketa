<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\View\CartView;

readonly class GetCartController
{
    public function __construct(
        public CartView $cartView,
        public CartManager $cartManager,
    ) {}

    public function get(): ResponseInterface
    {
        $cart = $this->cartManager->getCart();

        if (!$cart) {
            return $this->errorResponse('Cart not found', 404);
        }

        return $this->okResponse(json_encode(
            $this->cartView->toArray($cart),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ));
    }

    private function okResponse(string $message): JsonResponse // TODO: вынести метод в отдельный класс, чтобы избежать дублирования
    {
        $response = new JsonResponse();
        $response->getBody()->write($message);

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
    }

    private function errorResponse(string $message, int $code = 500): JsonResponse // TODO: вынести метод в отдельный класс, чтобы избежать дублирования
    {
        $response = new JsonResponse();
        $response->getBody()->write(
            json_encode(
                [
                    'status' => 'error',
                    'message' => $message
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($code);
    }
}
