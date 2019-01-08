<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \Faker\Factory
     */
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPost($manager);

    }

    public function loadBlogPost(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++){

            /** @var User $user */
            $user = $this->getReference('user_' . $i);
            for($j = 0; $j < random_int(1, 100); $j++) {
                $post = new BlogPost();
                $post->setTitle($this->faker->realText(30));
                $post->setContent($this->faker->realText());
                $post->setPublished($this->faker->dateTimeBetween('-1 year', 'now'));
                $post->setSlug($this->faker->slug);
                $post->setAuthor($user);

                $manager->persist($post);
                $manager->flush();

                $this->loadComments($manager, $post);
            }

        }
        $manager->flush();
    }

    public function loadComments(ObjectManager $manager, BlogPost $post)
    {
        for ($i = 0; $i < random_int(1, 30); $i++) {
            $comment = new Comment();
            $comment->setContent($this->faker->realText());
            $comment->setPublished($this->faker->dateTimeBetween($post->getPublished(), 'now'));
            $comment->setBlogPost($post);
            $comment->setAuthor($this->getReference('user_' . random_int(0, 9)));
            $manager->persist($comment);
        }
        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++){
            $user = new User();
            $user->setUsername($this->faker->userName);
            $user->setEmail($this->faker->email);
            $user->setName($this->faker->name);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user, 'haslo_' . $i
                )
            );
            $manager->persist($user);
            $this->addReference('user_' . $i, $user);

        }
        $manager->flush();
    }
}
