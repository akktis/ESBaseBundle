<?php

namespace ES\Bundle\BaseBundle\Features\Context;

use Beaumarly\SiteBundle\Entity\Booking;
use Beaumarly\SiteBundle\Entity\BookingCache;
use Beaumarly\SiteBundle\Entity\Establishment;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Event\StepEvent;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\SecurityBundle\Tests\Functional\app\AppKernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Exception;
use Doctrine\ORM\EntityRepository;
use Behat\Behat\Hook\Annotation\AfterStep;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Feature context.
 */
class ESFeatureContext extends MinkContext
                       implements KernelAwareInterface
{
    /** @var AppKernel */
    protected $kernel;

    /** @var array */
    protected $parameters;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Get repository of given entity
     * @param string $entity
	 * @return EntityRepository
     */
    protected function getRepository($entity)
	{
        return $this->kernel->getContainer()->get('doctrine')->getRepository($entity);
    }

    /**
     * Get doctrine manager
     * @return ObjectManager
     */
    protected function getManager()
	{
        return $this->kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Serialize array content in Guerkin format
     * @param array $array Key-value array
     * @return string
     */
    protected function arrayToStr($array)
    {
        $exp = "|";

        $sorted_keys = array_keys($array);
        sort($sorted_keys);

        foreach($sorted_keys as $key) $exp .= " " . $key . " |";

        $exp .= "\n|";

        foreach($sorted_keys as $key) $exp .= " " . $array[$key] . " |";

        return $exp;
    }

	/**
	 * Convert Behat table to php associative array
	 * @param TableNode $table
	 * @return array
	 */
    protected function convertTable(TableNode $table)
	{
		$rows = $table->getRows();

		if (count($rows) == 2) {
			$result = array();

			$keys = $rows[0];
			$values = $table->getRow(1);

			for ($i = 0; $i < sizeof($keys); ++$i) {
				$result[$keys[$i]] = $values[$i];
			}

			return $result;
		} else {
			return null;
		}
	}

	/**
	 * @Then /^I should see a popin$/
	 */
	public function iShouldSeeAPopin()
	{
		$this->getSession()->wait(3000, "$('.modal:visible').children().length > 0");
	}

    /**
     * Wait for all ajax request to finish
     * can be called in a function with an AfterStep annotation
     * Warning: Will proceed when ajax request is over, but before the callback is called...
     * @param StepEvent stepEvent
     */
    protected function waitForAjax(StepEvent $event) {
        $text = $event->getStep()->getText();
        if (preg_match('/(select|fill|press)/i', $text)) {
            $this->getSession()->wait(3000, '(0 === jQuery.active)');
        }
    }

    /**
     * Wait for element to be enabled
     * Usefull
     * @param $locator
     */
    protected function waitForEnabled($locator) {
        $object = $this->getSession()->getPage()->findField($locator);
        $this->getSession()->wait(3000, "! $('#" . $object->getAttribute('id') . "').disabled");
    }

    /**
     * {@inheritdoc}
     */
    public function fillField($field, $value)
    {
        // Add a wait for enabled
        $this->waitForEnabled($field);

        parent::fillField($field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($select, $option)
    {
        // Add a wait for enabled
        $this->waitForEnabled($select);

        parent::selectOption($select, $option);
    }

    /**
     * @When /^I take a ScreenShot "([^"]*)"$/
     */
    public function takeAScreenShot($name)
    {
//        if (!($driver instanceof Selenium2Driver)) {
//            //throw new UnsupportedDriverActionException('Taking screenshots is not supported by %s, use Selenium2Driver instead.', $driver);
//            return;
//        }

        $sc_dir = "var/sc";

        if (! file_exists($sc_dir)) mkdir($sc_dir);

        file_put_contents($sc_dir . '/' . $name . '.png', $this->getSession()->getDriver()->getScreenshot());
    }

    /**
     * @Then /^See a link to "([^"]*)"$/
     */
    public function hasLinkTo($url)
    {
        return new Then ( "I should see an \"a[href='$url']\" element" );
    }

    /**
     * @Then /^See links to:$/
     */
    public function hasLinksTo(TableNode $urlTbl)
    {
        foreach ($urlTbl->getRows() as $line) {
            foreach ($line as $url) {
                $this->assertSession()->elementExists('css', "a[href='$url']");
            }
        }
    }

    /**
     * @Then /^See no link to "([^"]*)"$/
     */
    public function hasNoLink($url)
    {
        return new Then ( "I should not see an \"a[href='$url']\" element" );
    }

    /**
     * @Then /^See no links to:$/
     */
    public function hasNoLinksTo(TableNode $urlTbl)
    {
        foreach ($urlTbl->getRows() as $line) {
            foreach ($line as $url) {
                $this->assertSession()->elementNotExists('css', "a[href='$url']");
            }
        }
    }

    /**
     * Checks, that pages contains specified text.
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" on:$/
     */
    public function assertPageContainsTextOn($text, TableNode $urlTbl)
    {
        foreach ($urlTbl->getRows() as $line) {
            foreach ($line as $url) {
                $this->getSession()->visit($this->locatePath($url));
                $this->assertSession()->pageTextContains($this->fixStepArgument($text));
            }
        }
    }

    /**
     * Checks, that page doesn't contain specified text.
     *
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)" on:$/
     */
    public function assertPageNotContainsTextOn($text, TableNode $urlTbl)
    {
        foreach ($urlTbl->getRows() as $line) {
            foreach ($line as $url) {
                $this->getSession()->visit($this->locatePath($url));
                $this->assertSession()->pageTextNotContains($this->fixStepArgument($text));
            }
        }
    }
}
