<?php

namespace PJM\AppBundle\Tests;

use PJM\AppBundle\Services\Payments\LydiaProvider;
use PJM\AppBundle\Tests\Services\BaseTest;
use Symfony\Component\HttpFoundation\Request;

class LydiaProviderTest extends BaseTest
{
    /** @var LydiaProvider $lydiaProvider */
    private $lydiaProvider;

    public function setUp()
    {
        self::bootKernel();
        $this->lydiaProvider = static::$kernel->getContainer()->get('pjm.services.payments.lydia');
    }

    public function testGetParamsCallback()
    {
        // it is a private method
        $getParamsCallback = self::getMethodFromClass('getParamsCallback', get_class($this->lydiaProvider));
        $auth = self::getPropertyFromClass('auth', get_class($this->lydiaProvider))->getValue($this->lydiaProvider);

        $params = array(
            'order_ref' => '563bcc_9',
            'request_id' => '3338',
            'amount' => '10.00',
            'currency' => 'EUR',
            'vendor_token' => $auth['pians']['public_token'],
            'signed' => '0',
            'sig' => '8e12f4f8b681307f4c0888a14f35f012',
        );
        $request = new Request(array(), $params);

        $expectedParams = $params;
        unset($expectedParams['sig']);

        $this->assertEquals($getParamsCallback->invokeArgs($this->lydiaProvider, array($request)), $expectedParams);
    }
}
