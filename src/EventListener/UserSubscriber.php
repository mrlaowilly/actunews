<?php


namespace App\EventListener;

    use App\Entity\Category;
    use App\Entity\Post;
    use App\Entity\User;
    use Doctrine\ORM\Events;
    use Doctrine\Persistence\Event\LifecycleEventArgs;
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;
    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserSubscriber implements \Doctrine\Common\EventSubscriber
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(UserPasswordEncoderInterface $encoder, MailerInterface $mailer)
    {
        $this->encoder = $encoder;
        $this->mailer = $mailer;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postPersist
        ];
    }

    /**
     * Cette fonction se déclenche juste avant l'insertion
     * d'un élément dans la BDD.
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->encodePassword($args);
        $this->generateAlias($args);
        $this->generateAliasCategory($args);
    }

    /**
     * Cette fonction se déclenche juste après l'insertion
     * d'un élément dans la BDD.
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->sendWelcomeEmail($args);
    }

    public function generateAlias(LifecycleEventArgs $args)
    {
        # 1. Récupération de l'Objet concerné
        $entity = $args->getObject();
        # 2. Si mon objet n'est pas une instance de "Post" on quitte.
        if (!$entity instanceof Post) {
            return;
        }
        //3. Formater l'alias du post
        function slugify($string){
            return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
        }
        $entity->setAlias(slugify($entity->getTitle()));
    }

    public function generateAliasCategory(LifecycleEventArgs $args)
    {
        # 1. Récupération de l'Objet concerné
        $entity = $args->getObject();
        # 2. Si mon objet n'est pas une instance de "Category" on quitte.
        if (!$entity instanceof Category) {
            return;
        }
        //3. Formater l'alias de la category
        function aliascategory($string)
        {
            return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
        }
        $entity->setAlias(aliascategory($entity->getName()));
    }


    /**
     * Permet d'encoder un mot de passe dans la BDD
     * juste avant l'insertion d'un User.
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

    /**
     * Permet l'envoi d'un email de bienvenue
     * https://symfony.com/doc/current/mailer.html#creating-sending-messages
     * TODO : Mettre en place un service dédié pour cela.
     * @param LifecycleEventArgs $args
     */
    private function sendWelcomeEmail(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $email = (new Email())
            ->from('noreply@actu.news')
            ->to($entity->getEmail())
            ->subject('Bienvenue sur notre site Actunews !')
            ->html('<p>Bonjour, Bienvenue chez ActuNews !</p>');

        $this->mailer->send($email);
    }
}
