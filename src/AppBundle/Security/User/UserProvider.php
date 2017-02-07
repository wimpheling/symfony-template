<?php
// src/AppBundle/Security/User/WebserviceUserProvider.php
namespace AppBundle\Security\User;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    private $container;

    public function __construct($container)
    {
      $this->container = $container;
    }

    public function loadUserByUsername($username)
    {
        $doctrine = $this->container->get('doctrine');
        $user = $doctrine->getRepository('AppBundle:User')
        ->find($username);
        // make a call to your webservice here
        // pretend it returns an array on success, false if there is no user

        if ($user) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('UserId "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
