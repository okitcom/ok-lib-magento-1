<?php
/**
 * Created by PhpStorm.
 * Date: 9/15/17
 */

namespace OK\Tests\Builder;


use OK\Builder\AttributeBuilder;
use OK\Builder\LineItemBuilder;
use OK\Builder\TransactionBuilder;
use OK\Model\Amount;
use OK\Model\Attribute;
use PHPUnit\Framework\TestCase;

class TransactionBuilderTest extends TestCase
{

    public function testBasicBuilder() {
        $builder = new TransactionBuilder();
        $response = $builder->setAmount(Amount::fromEuro(1.50))
            ->setCurrency("EUR")
            ->build();

        $this->assertEquals(1.50, $response->amount->getEuro());
        $this->assertEquals("EUR", $response->currency);
    }

    public function testCamelCase() {
        $builder = new TransactionBuilder();
        $response = $builder->setInitiationToken("TOKEN")
            ->build();

        $this->assertEquals("TOKEN", $response->initiationToken);
    }

    /**
     * @expectedException \OK\Builder\BuilderException
     */
    public function testNonExistingProperty() {
        $builder = new TransactionBuilder();
        $response = $builder->setXXYYZZ("AAA")
            ->build();

        $this->assertNull($response->xXYYZZ);
    }

    public function testAttributesLineItems() {
        $builder = new TransactionBuilder();
        $response = $builder->setReference("MERCHANT_REFERENCE")
            ->setType("LOGIN")
            ->setPermissions("NewPendingTrigger")
            ->addAttribute(
                (new AttributeBuilder())->setKey("KEY")
                    ->setLabel("My Key")
                    ->setType(Attribute::TYPE_STRING)
                    ->setRequired(false)
                    ->setSuggestedValue("Suggested")
                    ->build()
            )->addAttribute(
                (new AttributeBuilder())->setKey("NAME")
                    ->setLabel("Your name")
                    ->setType(Attribute::TYPE_NAME)
                    ->setRequired(true)
                    ->setSuggestedValue("Suggested Name")
                    ->build()
            )
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromCents(100))
                ->setDescription("LineItem")
                ->setProductCode("LI")
                ->setQuantity(1)
                ->setVat(1)
                ->build()
            )->build();

        $this->assertEquals("Suggested Name", $response->attributes->get("NAME")->suggestedValue);
    }
}