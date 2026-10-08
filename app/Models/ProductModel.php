<?php

declare(strict_types=1);

final class ProductModel
{
    public function catalog(): array
    {
        return [
            [
                'name' => 'Cargadores',
                'category' => 'Accesorios móviles',
                'brand' => 'Tecno Star',
                'price' => 18.99,
                'old_price' => 24.99,
                'is_offer' => true,
                'description' => 'Carga tus dispositivos de forma práctica y segura.',
                'icon' => 'bi-lightning-charge',
            ],
            [
                'name' => 'Fundas para celular',
                'category' => 'Accesorios móviles',
                'brand' => 'Tecno Star',
                'price' => 12.50,
                'old_price' => null,
                'is_offer' => false,
                'description' => 'Protección y estilo para diferentes modelos.',
                'icon' => 'bi-phone',
            ],
            [
                'name' => 'Audífonos',
                'category' => 'Audio y conectividad',
                'brand' => 'Apple',
                'price' => 24.99,
                'old_price' => 34.99,
                'is_offer' => true,
                'description' => 'Disfruta tu música, clases y llamadas.',
                'icon' => 'bi-headphones',
            ],
            [
                'name' => 'Cables USB',
                'category' => 'Audio y conectividad',
                'brand' => 'MODOFVY',
                'price' => 9.99,
                'old_price' => null,
                'is_offer' => false,
                'description' => 'Conecta, carga y transfiere tus archivos.',
                'icon' => 'bi-usb-plug',
            ],
            [
                'name' => 'Mouse y teclados',
                'category' => 'Tecnología',
                'brand' => 'Gamer',
                'price' => 34.99,
                'old_price' => 44.99,
                'is_offer' => true,
                'description' => 'Accesorios para estudiar, trabajar y crear.',
                'icon' => 'bi-keyboard',
            ],
            [
                'name' => 'Bocinas',
                'category' => 'Tecnología',
                'brand' => 'Bluetooth',
                'price' => 29.99,
                'old_price' => null,
                'is_offer' => false,
                'description' => 'Lleva tu sonido a donde quieras.',
                'icon' => 'bi-speaker',
            ],
        ];
    }
}
