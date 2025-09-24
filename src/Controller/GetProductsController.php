<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Repository\ProductRepository;
use Raketa\BackendTestTask\View\ProductsView;

readonly class GetProductsController
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductsView $productsView,
    ) {}

    public function get(RequestInterface $request): ResponseInterface
    {
        $rawRequest = json_decode($request->getBody()->getContents(), true); // В идеале, нужно сделать метод получения декодированных данных сразу из класса реквеста

        if (is_null($rawRequest)) {
            return $this->errorResponse('Не удалось раскодировать предоставленные данные', 400);
        }

        $products = $this->productRepository->getByCategory($rawRequest['category']);

        return $this->okResponse(json_encode(
            $this->productsView->toArray($products),
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
