<?php

namespace PJM\AppBundle\Tests;

use PJM\AppBundle\Services\Trads;
use PJM\AppBundle\Tests\Services\BaseTest;

class TradsTest extends BaseTest
{
    /** @var Trads $trads */
    private $trads;

    public function setUp()
    {
        self::bootKernel();
        $this->trads = static::$kernel->getContainer()->get('pjm.services.trads');
    }

    /**
     * @dataProvider famsProvider
     *
     * @param string $fams
     * @param array  $expectedNums
     */
    public function testGetNums($fams, array $expectedNums)
    {
        $this->assertEquals($expectedNums, $this->trads->getNums($fams));
    }

    public function famsProvider()
    {
        return array(
            array('26#68', array(26, 68)),
            array('40', array(40)),
            array('95-157', array(95, 157)),
            array('XIII-57-142', array(13, 57, 142)),
            array('5!', array(120)),
            array('4!-43-92', array(24, 43, 92)),
            array('168bis', array(169)),
            array('1#-2', array(1, 2)),
            array('1 2', array(1, 2)),
            array('2-1', array(1, 2)),
        );
    }

    public function testGetExanceFromDate()
    {
        $this->assertEquals(0, $this->trads->getExanceFromDate(new \DateTime('June 5th 2015')));
        $this->assertEquals(1, $this->trads->getExanceFromDate(new \DateTime('June 4th 2015')));
        $this->assertGreaterThan(360, $this->trads->getExanceFromDate(new \DateTime('June 6th 2015')));
    }
}
