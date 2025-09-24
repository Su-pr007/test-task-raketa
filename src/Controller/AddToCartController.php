<?php

namespace Raketa\BackendTestTask\Controller;

use Doctrine\DBAL\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Domain\CartItem;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\Repository\ProductRepository;
use Raketa\BackendTestTask\View\CartView;
use Ramsey\Uuid\Uuid;

readonly class AddToCartController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartView $cartView,
        private CartManager $cartManager,
    ) {}

    public function add(JsonResponse $request): ResponseInterface // Название метода лучше сделать `add`
    {
        $rawRequest = json_decode($request->getBody()->getContents(), true);

        if (is_null($rawRequest)) {
            return $this->errorResponse('Не удалось раскодировать предоставленные данные', 400);
        }

        // "Валидация"
        if (empty($rawRequest['productUuid']) || empty($rawRequest['quantity'])) {
            return $this->errorResponse('Переданы не все данные', 400);
        }

        $product = $this->productRepository->getByUuid($rawRequest['productUuid']); // TODO: Конкретные действия с сущностями лучше делать в сервисах. В контроллере лишь передача данных в сервис, и возврат ответа пользователю

        if (is_null($product)) { // Проверка на нахождение товара
            return $this->errorResponse('Товар не найден', 404);
        }

        $cart = $this->cartManager->getCart();
        $cart->addItem(new CartItem(
            Uuid::uuid4()->toString(),
            $product->getUuid(),
            $product->getPrice(),
            $rawRequest['quantity'],
        ));

        return $this->okResponse(json_encode(
            [
                'status' => 'success',
                'cart' => $this->cartView->toArray($cart),
            ],
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
                    'message' => $message,
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($code);
    }
}
