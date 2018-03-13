<?php
/**
 * Created by PhpStorm.
 * Date: 7/21/17
 */

namespace OK\Tests;

use OK\Client;
use OK\Model\Network\Exception\NetworkException;
use OK\Model\Cash\TransactionRequest;
use OK\Model\TicketObject;
use OK\Service\Cash;
use OK\Service\Ticketing;

class TicketTest extends ServiceTest
{

    /** @var  Ticketing */
    protected $service;

    public function setUp() {
        parent::setUp();

        $this->service = new Ticketing($this->ticketCredentials);
    }

    public function testInitiate() {
        $this->assertNotNull($this->service);
    }

    // TODO :Implement



}
