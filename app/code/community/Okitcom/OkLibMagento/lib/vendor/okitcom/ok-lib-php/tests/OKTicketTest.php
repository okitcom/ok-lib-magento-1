<?php
/**
 * Created by PhpStorm.
 * Date: 7/21/17
 */

namespace OK\Tests;

use OK\Client;
use OK\JsonDateTime;
use OK\Model\Event;
use OK\Model\OKTicket\TicketPushResponse;
use OK\Model\TicketObject;
use OK\Service\OKTicket;

class OKTicketTest extends ServiceTest
{

    /** @var  OKTicket */
    protected $service;

    public function setUp() {
        parent::setUp();

        $this->service = new OKTicket($this->okticketCredentials);
    }



    public function testPushSuccess() {
        $this->markTestSkipped("Skipped because no valid user token");

        $startTime = new JsonDateTime();
        $endTime = new JsonDateTime("@" . ($startTime->getTimestamp() + 10000), $startTime->getTimezone());

        $event = new Event();
        $event->name = "PHPUnit event";
        $event->start = $startTime;
        $event->end = $endTime;
        $event->location = "47.170174,27.577120299999933";
        $event->venue = "Local";
        $event->conditions = "Terms and conditions may apply";
        $event->description = "This is an automated event";
        $event->externalId = "PHPUNITEVENT";

        $ticket = new TicketObject();
        $ticket->event = $event;
        $ticket->externalId = "PHPUNITOKLIB" . mt_rand();
        $ticket->barcodeType = "QR";
        $ticket->barcode = "PHPUNITOKLIB" . mt_rand();
        $ticket->data = "Ticket data";

        $request = new \OK\Model\OKTicket\TicketPushRequest();
        $request->token = $this->userToken;
        $request->tickets = [$ticket];
        $res = $this->service->push($request);

        $this->assertTrue($res instanceof TicketPushResponse);
    }

    /**
     * @expectedException \OK\Model\Network\Exception\NetworkException
     */
    public function testPushFail() {

        $request = new \OK\Model\OKTicket\TicketPushRequest();
        $request->token = "";
        $request->tickets = [];
        $res = $this->service->push($request);
    }
}
