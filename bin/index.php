<?php

require_once 'vendor/autoload.php';

$str = <<<EOD
{
    "store": {
    "book": [
            {
                "category": "reference",
                "author": "Nigel Rees",
                "title": "Sayings of the Century",
                "price": 8.95
            },
            {
                "category": "fiction",
                "author": "Evelyn Waugh",
                "title": "Sword of Honour",
                "price": 12.99
            },
            {
                "category": "fiction",
                "author": "Herman Melville",
                "title": "Moby Dick",
                "isbn": "0-553-21311-3",
                "price": 8.99
            },
            {
                "category": "fiction",
                "author": "J. R. R. Tolkien",
                "title": "The Lord of the Rings",
                "isbn": "0-395-19395-8",
                "price": 22.99
            }
        ],
        "bicycle": {
        "color": "red",
            "price": 19.95
        }
    }
}
EOD;

$arr = [
    'store' => [
        'book' => [
            [
                'category' => 'Category 01',
                'author' => 'Author 01'
            ], [
                'category' => 'Category 02',
                'author' => 'Author 02'
            ]
        ]
    ]
];

$std = new stdClass([
    'store' => new stdClass([
        'book' => new stdClass([
            new stdClass([
                'category' => 'Category 01',
                'author' => 'Author 01'
            ]),
            new stdClass([
                'category' => 'Category 02',
                'author' => 'Author 02'
            ])
        ])
    ])
]);

// $store = new \MSBios\Json\Store($arr);
$store = new \MSBios\Json\Store($str);
// $store = new \MSBios\Json\Store($std);
var_dump($store->find("$..store.book[0].category"));
