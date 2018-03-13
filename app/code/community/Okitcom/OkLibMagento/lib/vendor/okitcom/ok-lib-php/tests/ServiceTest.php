<?php
/**
 * Created by PhpStorm.
 * Date: 7/21/17
 */

namespace OK\Tests;
use OK\Credentials\CashCredentials;
use OK\Credentials\Environment\DevelopmentEnvironment;
use OK\Environment;
use OK\Credentials\OKTicketCredentials;
use OK\Credentials\OpenCredentials;
use OK\Credentials\TicketCredentials;

/**
 * Class ClientTest. Contains instantiated client object
 */
abstract class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CashCredentials
     */
    protected $cashCredentials;

    /**
     * @var OpenCredentials
     */
    protected $openCredentials;

    /**
     * @var TicketCredentials
     */
    protected $ticketCredentials;

    /**
     * @var OKTicketCredentials
     */
    protected $okticketCredentials;

    /**
     * @var string
     */
    protected $userToken = "294f27a9-4ab6-4848-b75e-a0ccf00f6799";

    protected $env;

    public function setUp() {
        parent::setUp();

        $this->env = new DevelopmentEnvironment();

        $this->openCredentials = new OpenCredentials("", getenv("KEY_OPEN"), $this->env);
        $this->cashCredentials = new CashCredentials("", getenv("KEY_CASH"), $this->env);
        $this->ticketCredentials = new TicketCredentials("", getenv("KEY_TICKET"), $this->env);
        $this->okticketCredentials = new OKTicketCredentials("", getenv("KEY_TICKET"), $this->env);
    }

}
