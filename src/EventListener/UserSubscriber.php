<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserSubscriber implements \Doctrine\Common\EventSubscriber
{

    /**
     * @var UserPasswordEncoderInterface
    */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;        
    }
    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist
        ];
    }

    /**
     * Cette fonction se déclenche juste avant l'insertion d'un élément dans la BDD.
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->encodePassword($args);
    }

    /**
     * Permet d'encoder un mot de passe dans la BDD juste avant l'insertion d'un User.
     * @param LifecycleEventArgs $args
     */
    public function encodePassword(LifecycleEventArgs $args)
    {
        # 1. Récupération de l'Objet concerné
        $entity = $args->getObject();

        # 2. Si mon objet n'est pas une instance de "User" on quitte.
        if (!$entity instanceof User) {
            return;
        }

        # 3. Sinon, on encode le mot de passe
        $entity->setPassword(
            $this->encoder->encodePassword(
                    $entity,
                    $entity->getPassword()
            )
        );
    }
}    