<?php

namespace PJM\AppBundle\Tests;

use PJM\AppBundle\Services\Payments\LydiaProvider;
use PJM\AppBundle\Tests\Services\BaseTest;

class LydiaProviderTest extends BaseTest
{
    /** @var LydiaProvider $lydiaProvider */
    private $lydiaProvider;

    public function setUp()
    {
        self::bootKernel();
        $this->lydiaProvider = static::$kernel->getContainer()->get('pjm.services.payments.lydia');
    }

    public function testGetCallSignature()
    {
        // it is a private method
        $getCallSignature = self::getMethodFromClass('getCallSignature', get_class($this->lydiaProvider));
        $vendorToken = self::getPropertyFromClass('vendorToken', get_class($this->lydiaProvider))->getValue($this->lydiaProvider);

        $sig = '123951c14b17c2068223e93d47f15696';
        $params = array(
            'order_ref' => '563bcc_9',
            'request_id' => '3338',
            'transaction_identifier' => '',
            'amount' => '10.00',
            'currency' => 'EUR',
            'vendor_token' => $vendorToken,
            'signed' => '0',
            'sig' => $sig,
        );

        $this->assertTrue($getCallSignature->invokeArgs($this->lydiaProvider, array($params)) == $sig);
    }
}
