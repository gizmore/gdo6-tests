<?php
namespace GDO\Core\Test;

use GDO\Tests\TestCase;
use GDO\Language\GDO_Language;
use GDO\Table\GDT_Table;

final class GDOSortTest extends TestCase
{
    public function testGDOSorting()
    {
        $gdos = GDO_Language::table()->all();
        GDT_Table::make()->addHeaders($gdos->gdo);
    }
    
}
