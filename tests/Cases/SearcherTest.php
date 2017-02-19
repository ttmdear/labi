<?php
namespace Labi\Tests\Cases;

use Labi\Tests\Bookstore\BookSearcher;
use Labi\Database\Searcher;

class SearcherTest extends \Labi\Tests\TestCase
{
    public function testInstanceOf()
    {
        $bookstore = self::adapter('bookstore');

        $this->assertInstanceOf(Searcher::class, $bookstore->searcher());
        $this->assertInstanceOf(Searcher::class, $bookstore->searcher(BookSearcher::class));
        $this->assertInstanceOf(BookSearcher::class, $bookstore->searcher(BookSearcher::class));
    }

    public function testSearcher()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
        ;

        $index = array(
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 1,
            7 => 1,
            8 => 1,
            9 => 1,
            10 => 1,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testIn()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcherIdCategory = $bookstore->searcher();
        $searcherIdCategory
            ->from('books Categories Dic')
            ->column('idCategory')
            ->context()
            ->eq('name', 'Science fiction')
        ;

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->in('idBook', array(1,2,3))
            ->in('idCategory', $searcherIdCategory)
        ;

        $index = array (
            // 1 => true,
            2 => true,
            // 3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testNotIn()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcherIdCategory = $bookstore->searcher();
        $searcherIdCategory
            ->from('books Categories Dic')
            ->column('idCategory')
            ->context()
            ->eq('name', 'Science fiction')
        ;

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->notIn('idBook', array(1,2,3))
            ->brackets(function($searcher) use($searcherIdCategory){
                $searcher->orOperator();
                $searcher->notIn('idCategory', $searcherIdCategory);
                $searcher->isNull('idCategory');
            })
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            4 => true,
            5 => true,
            6 => true,
            7 => true,
            // 8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testIsNull()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->isNull('idCategory')
        ;

        $index = array (
            1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            5 => true,
            6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testIsNotNull()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->isNotNull('idCategory')
        ;

        $index = array (
            // 1 => true,
            2 => true,
            3 => true,
            4 => true,
            // 5 => true,
            // 6 => true,
            7 => true,
            8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testStartWith()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        // zmieniam jedna nazwe
        $updater = $bookstore->updater();
        $updater
            ->table('books')
            ->values(array(
                'name' => 'test'
            ))
            ->eq('idBook', 4)
        ;

        $updater->update();

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->startWith('name', 't')
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            4 => true,
            // 5 => true,
            // 6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testEndWith()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->endWith('name', '8')
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            // 7 => true,
            // 8 => true,
            9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testContains()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->contains('name', '- 6')
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testLike()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->contains('name', '%- 6%')
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testEq()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->eq('name', 'name - 6')
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testNeq()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->neq('name', 'name - 6')
        ;

        $index = array (
            1 => true,
            2 => true,
            3 => true,
            4 => true,
            5 => true,
            6 => true,
            // 7 => true,
            8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testLt()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->lt('idBook', 4)
        ;

        $index = array (
            1 => true,
            2 => true,
            3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testLte()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->lte('idBook', 4)
        ;

        $index = array (
            1 => true,
            2 => true,
            3 => true,
            4 => true,
            // 5 => true,
            // 6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testGt()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->gt('idBook', 4)
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            5 => true,
            6 => true,
            7 => true,
            8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testGte()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->gte('idBook', 4)
        ;

        $index = array (
            // 1 => true,
            // 2 => true,
            // 3 => true,
            4 => true,
            5 => true,
            6 => true,
            7 => true,
            8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testExpr()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->expr('(idBook = :idBook or name like :like)')
        ;

        $searcher->param('idBook', 1);
        $searcher->param('like', '%name - 7%');

        $index = array (
            1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            // 5 => true,
            // 6 => true,
            // 7 => true,
            8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testBetween()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->between('idBook', 2, 6)
        ;

        $index = array (
            // 1 => true,
            2 => true,
            3 => true,
            4 => true,
            5 => true,
            6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testExists()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcherIdCategory = $bookstore->searcher();
        $searcherIdCategory
            ->from('books Categories Dic', 'categories')
            ->column('idCategory')
            ->context()
            // ->eq('name', 'Science fiction')
            ->expr('categories.idCategory = books.idCategory')
        ;

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->exists($searcherIdCategory)
        ;

        $index = array (
            // 1 => true,
            2 => true,
            3 => true,
            4 => true,
            // 5 => true,
            // 6 => true,
            7 => true,
            8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }

    public function testNotExists()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcherIdCategory = $bookstore->searcher();
        $searcherIdCategory
            ->from('books Categories Dic', 'categories')
            ->column('idCategory')
            ->context()
            // ->eq('name', 'Science fiction')
            ->expr('categories.idCategory = books.idCategory')
        ;

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->notExists($searcherIdCategory)
        ;

        $index = array (
            1 => true,
            // 2 => true,
            // 3 => true,
            // 4 => true,
            5 => true,
            6 => true,
            // 7 => true,
            // 8 => true,
            // 9 => true,
            // 10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);
    }

    public function testBrackets()
    {
        $this->reset();

        $bookstore = self::adapter('bookstore');

        $searcher = $bookstore->searcher();
        $searcher
            ->from('books')
            ->eq('idBook', 1)
            ->orOperator()
            ->brackets(function($searcher){
                $searcher->orOperator();
                $searcher->eq('idBook', 2);

                $searcher->brackets(function($searcher){
                    $searcher->orOperator();
                    $searcher->eq('idBook', 3);

                    $searcher->brackets(function($searcher){
                        $searcher->orOperator();
                        $searcher->eq('idBook', 4);
                        $searcher->brackets(function($searcher){
                            $searcher->orOperator();
                            $searcher->eq('idBook', 5);
                            $searcher->brackets(function($searcher){
                                $searcher->orOperator();
                                $searcher->eq('idBook', 6);
                                $searcher->brackets(function($searcher){
                                    $searcher->orOperator();
                                    $searcher->eq('idBook', 7);
                                    $searcher->brackets(function($searcher){
                                        $searcher->orOperator();
                                        $searcher->eq('idBook', 8);
                                        $searcher->brackets(function($searcher){
                                            $searcher->orOperator();
                                            $searcher->eq('idBook', 9);
                                        });
                                    });
                                });
                            });
                        });
                    });
                });
            })
            ->brackets(function($searcher){
                $searcher->eq('idBook', 10);
            });
        ;

        $index = array (
            1 => true,
            2 => true,
            3 => true,
            4 => true,
            5 => true,
            6 => true,
            7 => true,
            8 => true,
            9 => true,
            10 => true,
        );

        foreach ($searcher->search() as $row) {
            $this->assertArrayHasKey($row['idBook'], $index);

            unset($index[$row['idBook']]);
        }

        $this->assertEquals(count($index), 0);

    }
}
