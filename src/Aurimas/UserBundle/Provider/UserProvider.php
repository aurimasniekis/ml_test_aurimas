<?php

namespace Aurimas\UserBundle\Provider;

use Aurimas\UserBundle\Entity\User;
use Aurimas\UserBundle\Entity\UserRepository;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 * @package Aurimas\UserBundle\Provider
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class UserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @return UserRepository
     */
    public function getUserRepository()
    {
        if ($this->userRepository) {
            return $this->userRepository;
        }

        $this->userRepository = $this->getEntityManager()->getRepository('AurimasUserBundle:User');

        return $this->userRepository;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return $this
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getNickname();

        $user = $this->getUserRepository()->findByUsername($username);
        $em = $this->getEntityManager();

        if (count($user) < 1) {
            $user = new User();
            $user->setUsername($username);

        } else {
            $user = reset($user);
        }

        $user->setToken($response->getAccessToken());

        $em->persist($user);
        $em->flush();

        $em->getConfiguration()->getResultCacheImpl()->delete('user_' . $username);

        return $user;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        $user = $this->getUserRepository()->findByUsername($username);

        if (count($user) < 1) {
            throw new UsernameNotFoundException();
        }

        $user = reset($user);

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        $user = $this->getUserRepository()->findByUsername($user->getUsername());

        if (count($user) < 1) {
            throw new UsernameNotFoundException();
        }

        $user = reset($user);

        return $user;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return (bool)($class == '\Aurimas\UserBundle\Entity\User');
    }
}
