<?php
/**
 * Created by PhpStorm.
 * Date: 7/21/17
 */

namespace OK\Tests;

use OK\Builder\LineItemBuilder;
use OK\Builder\TransactionBuilder;
use OK\Model\Amount;
use OK\Model\Cash\LineItem;
use OK\Model\Cash\LineItems;
use OK\Model\Cash\Transaction;
use OK\Service\Cash;

class CashTest extends ServiceTest
{

    /** @var  Cash */
    protected $service;

    public function setUp() {
        parent::setUp();

        $this->service = new Cash($this->cashCredentials);
    }


    public function testGetStatus() {
        $this->markTestSkipped("Yet to find a valid transaction");
        $result = $this->service->status("XzPvkTBQQGy_eweAtKvQ2w");
        $this->assertEquals("ClosedAndCaptured", $result->state);
    }

    /**
     * @expectedException \OK\Model\Network\Exception\NetworkException
     */
    public function testGetGuidFail() {
        $this->service->status("XXX");
    }

    public function testGetTransaction() {
        $this->markTestSkipped("Yet to find a valid transaction");
        $result = $this->service->get("XzPvkTBQQGy_eweAtKvQ2w");
        $this->assertEquals("OK", $result->authorisationResult->result);
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testInitiate() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx")
            ->build();

        $res = $this->service->request($request);
        $this->assertNotNull($res->guid);
        $this->assertEquals("NewPendingTrigger", $res->state);
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testQR() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx")
            ->build();

        $res = $this->service->request($request);

        $qr = $this->service->qr($res->guid);
        $this->assertNotNull($qr);
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testCancel() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx")
            ->build();

        $res = $this->service->request($request);
        $canceled = $this->service->cancel($res->guid);
        $this->assertTrue($canceled->success());
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testCancelByReference() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx " . mt_rand())
            ->build();

        $res = $this->service->request($request);
        $canceled = $this->service->cancelByReference($res->reference);
        $this->assertTrue($canceled->success());
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testGetByReference() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx ref " . mt_rand())
            ->build();

        $response = $this->service->request($request);
        $txs = $this->service->getByReference($request->reference);
        $this->assertEquals($response->guid, $txs->guid);
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testGetLineItemsOne() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx")
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromEuro(10.00))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Beschrijving")
                    ->build()
            )
            ->build();

        $result = $this->service->request($request);

        $this->assertNotNull($result->lineItems->all());
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testGetLineItemsTwo() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1500))
            ->setReference("PHPUnit tx")
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromEuro(10.00))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Beschrijving")
                    ->build()
            )
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromEuro(5.00))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Beschrijving 2")
                    ->build()
            )
            ->build();

        $result = $this->service->request($request);

        $this->assertEquals(2, count($result->lineItems->all()));
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testGetLineItemsExcludedFromCampaigns() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("PHPUnit tx")
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromEuro(10.00))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Beschrijving")
                    ->setExcludedFromCampaigns(true)
                    ->build()
            )
            ->build();

        $result = $this->service->request($request);

        $this->assertTrue($result->lineItems->all()[0]->excludedFromCampaigns);
    }

    /**
     * @throws \OK\Model\Network\Exception\NetworkException
     */
    public function testLineItemsDiscount() {
        $request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1250))
            ->setReference("PHPUnit tx")
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromCents(1000))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Beschrijving")
                    ->addSubItem(
                        (new LineItemBuilder())
                        ->setAmount(Amount::fromCents(-250))
                        ->setVat(0)
                        ->setCurrency("EUR")
                        ->setQuantity(1)
                        ->setDescription("Discount")
                        ->build()
                    )
                    ->build()
            )
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromCents(500))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Beschrijving 2")
                    ->build()
            )
            ->build();

        $result = $this->service->request($request);

        $this->assertEquals(2, count($result->lineItems->all()));
    }

}
