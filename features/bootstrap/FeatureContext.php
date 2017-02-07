<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use AppBundle\Entity\User;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Assert\Assertion;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{
    use KernelDictionary;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct($session)
    {
      $this->session = $session;
    }

    /**
     * @Given un utilisateur avec l'e-mail :arg1 et le mot de passe :arg2
     */
    public function unUtilisateurAvecLEMailEtLeMotDePasse($arg1, $arg2)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $admin = new User;
        $encoder = $this->getContainer()->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($admin, $arg2);
        $admin->setEmail($arg1)->setMotDePasse($encodedPassword);
        $em = $doctrine->getManager();
        $em->persist($admin);
        $em->flush();
    }

    /**
     * @param $arg1
     * @param $arg2
     * @return Correspondant
     */
    private function getClient($arg1, $arg2)
    {
        //Get client
        $doctrine = $this->getContainer()->get('doctrine');
        $client = $doctrine->getRepository('AppBundle:Client')->findOneBy([]);
        $correspondant = new Correspondant;
        $encoder = $this->getContainer()->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($correspondant, $arg2);

        $correspondant->setRole(Correspondant::ROLE_ADMIN)->setClient($client)->setEmail($arg1)->setMotDePasse($encodedPassword);
        return $correspondant;
    }

    /**
     * @When je suis connecté avec :arg1
     */
    public function jeSuisConnecteAvec($arg1)
    {
      $this->getSession()->visit('/');
      $this->fillField('form[email]', $arg1);
      $this->fillField('form[motDePasse]', 'password');
      $this->pressButton('Me connecter');
    }

    /**
     * @Then un mail devrait avoir été envoyé à :destinataire, intitulé :titre
     */
    public function unMailDevraitAvoirEteEnvoyeAIntitule($destinataire, $titre)
    {
        $error     = sprintf('No message sent to "%s"', $destinataire);
        $profile   = $this->getSymfonyProfile();
        /** @var \Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector $collector */
        $collector = $profile->getCollector('swiftmailer');

        foreach ($collector->getMessages() as $message) {
            // Checking the recipient email and the X-Swift-To
            // header to handle the RedirectingPlugin.
            // If the recipient is not the expected one, check
            // the next mail.
            /** @var Swift_Message $message */
            $correctRecipient = array_key_exists(
                $destinataire, $message->getTo()
            );
            $headers = $message->getHeaders();
            $correctXToHeader = false;
            if ($headers->has('X-Swift-To')) {
                $correctXToHeader = array_key_exists($destinataire,
                    $headers->get('X-Swift-To')->getFieldBodyModel()
                );
            }

            if (!$correctRecipient && !$correctXToHeader) {
                continue;
            }
            try {
                // checking the content
                return Assertion::eq($titre, $message->getSubject());
            } catch (AssertException $e) {
                $error = sprintf(
                    'An email has been found for "%s" but without '.
                    'the text "%s".', $destinataire, $titre->getRaw()
                );
            }
        }

        throw new \Behat\Mink\Exception\ExpectationException($error, $this->getSession());
    }

    /**
     * @return mixed
     * @throws UnsupportedDriverActionException
     */
    public function getSymfonyProfile()
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof KernelDriver) {
            throw new UnsupportedDriverActionException(
                'You need to tag the scenario with '.
                '"@mink:symfony2". Using the profiler is not '.
                'supported by %s', $driver
            );
        }

        $profile = $driver->getClient()->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'The profiler is disabled. Activate it by setting '.
                'framework.profiler.only_exceptions to false in '.
                'your config'
            );
        }

        return $profile;
    }

    /**
     * @Then je suis la redirection
     */
    public function jeSuisLaRedirection()
    {
        $client = $this->getSession()->getDriver()->getClient();
        $client->followRedirect();
        $this->getSession()->getDriver()->getClient()->followRedirects(true);
    }

    /**
     * @Then je désactive les redirections
     */
    public function jeDesactiveLesRedirections()
    {
        $this->getSession()->getDriver()->getClient()->followRedirects(false);
    }
}
