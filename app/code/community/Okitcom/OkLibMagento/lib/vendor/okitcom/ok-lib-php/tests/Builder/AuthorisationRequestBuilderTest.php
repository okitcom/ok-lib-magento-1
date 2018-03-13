<?php
/**
 * Created by PhpStorm.
 * Date: 9/15/17
 */

namespace OK\Tests\Builder;


use OK\Builder\AttributeBuilder;
use OK\Builder\AuthorisationRequestBuilder;
use OK\Model\Attribute;
use PHPUnit\Framework\TestCase;

class AuthorisationRequestBuilderTest extends TestCase
{

    public function testBasicBuilder() {
        $builder = new AuthorisationRequestBuilder();
        $response = $builder->setAction("LOGIN")
            ->setReference("My Reference")
            ->build();

        $this->assertEquals("LOGIN", $response->action);
        $this->assertEquals("My Reference", $response->reference);
    }

    public function testCamelCase() {
        $builder = new AuthorisationRequestBuilder();
        $response = $builder->setTriggerPeriod("100")
            ->build();

        $this->assertEquals("100", $response->triggerPeriod);
    }

    /**
     * @expectedException \OK\Builder\BuilderException
     */
    public function testNonExistingProperty() {
        $builder = new AuthorisationRequestBuilder();
        $response = $builder->setXXYYZZ("AAA")
            ->build();

        $this->assertNull($response->xXYYZZ);
    }

    public function testAttributes() {
        $builder = new AuthorisationRequestBuilder();
        $response = $builder->setReference("merchantRef123")
            ->setAction("LOGIN")
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
            )->build();

        $this->assertEquals("Suggested Name", $response->attributes->get("NAME")->suggestedValue);
    }

}