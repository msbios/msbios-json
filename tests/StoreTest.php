<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */
namespace MSBiosTest\Json;

use MSBios\Json\Store;
use PHPUnit\Framework\TestCase;

/**
 * Class StoreTest
 * @package MSBiosTest\Json
 */
class StoreTest extends TestCase
{
    public function testWorkWithArray()
    {
        $store = new Store([
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
        ]);
        $this->assertEquals('Category 02', $store->find("$..store.book[1].category"));
    }
}
