<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Диагностика',
                'description' => 'Комплексная компьютерная и инструментальная диагностика всех систем автомобиля. Выявляем неисправности до того, как они станут проблемой.',
                'sort_order' => 10,
            ],
            [
                'name' => 'ТО и обслуживание',
                'description' => 'Плановое техническое обслуживание по регламенту производителя. Замена расходников, проверка ключевых узлов, продление ресурса автомобиля.',
                'sort_order' => 20,
            ],
            [
                'name' => 'Ремонт двигателя',
                'description' => 'Капитальный и текущий ремонт ДВС: от замены прокладок и сальников до полной переборки. Работаем с бензиновыми и дизельными моторами.',
                'sort_order' => 30,
            ],
            [
                'name' => 'Тормозная система',
                'description' => 'Обслуживание и ремонт тормозов: колодки, диски, суппорты, шланги. Прокачка тормозной жидкости и диагностика ABS.',
                'sort_order' => 40,
            ],
            [
                'name' => 'Электрика',
                'description' => 'Диагностика и ремонт электрооборудования: генератор, стартер, проводка, мультимедиа. Восстановление работоспособности любых электронных систем.',
                'sort_order' => 50,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $data) {
            $createdCategories[] = Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, [
                    'slug' => Str::slug($data['name']),
                    'active' => true,
                ])
            );
        }

        // По одной услуге в каждую категорию — 5 услуг суммарно.
        $services = [
            [
                'category_id' => $createdCategories[0]->id,
                'name' => 'Полная компьютерная диагностика',
                'description' => 'Считывание ошибок всех блоков управления, проверка датчиков, анализ параметров работы двигателя и трансмиссии. По итогам — подробный отчёт.',
                'duration_minutes' => 60,
                'price' => 2500,
            ],
            [
                'category_id' => $createdCategories[1]->id,
                'name' => 'ТО-1: замена масла и фильтров',
                'description' => 'Замена моторного масла, масляного и воздушного фильтров. Визуальный осмотр подкапотного пространства и долив технических жидкостей.',
                'duration_minutes' => 45,
                'price' => 1800,
            ],
            [
                'category_id' => $createdCategories[2]->id,
                'name' => 'Замена ремня ГРМ',
                'description' => 'Замена ремня газораспределительного механизма с роликами и помпой. Точная установка по меткам, проверка натяжения и контроль момента затяжки.',
                'duration_minutes' => 240,
                'price' => 8500,
            ],
            [
                'category_id' => $createdCategories[3]->id,
                'name' => 'Замена тормозных колодок',
                'description' => 'Замена передних или задних тормозных колодок. Проверка состояния дисков и суппортов, при необходимости — прокачка системы.',
                'duration_minutes' => 90,
                'price' => 3200,
            ],
            [
                'category_id' => $createdCategories[4]->id,
                'name' => 'Диагностика и ремонт стартера',
                'description' => 'Снятие, разборка и диагностика стартера. Замена щёток, втягивающего реле или бендикса, проверка обмоток. Сборка и установка обратно.',
                'duration_minutes' => 120,
                'price' => 4500,
            ],
        ];

        foreach ($services as $i => $data) {
            Service::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, [
                    'slug' => Str::slug($data['name']),
                    'active' => true,
                    'sort_order' => ($i + 1) * 10,
                ])
            );
        }
    }
}
