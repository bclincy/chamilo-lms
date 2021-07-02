<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\Tests\CoreBundle\Repository;

use Chamilo\CoreBundle\Entity\MessageTag;
use Chamilo\CoreBundle\Repository\MessageTagRepository;
use Chamilo\CoreBundle\Repository\Node\UserRepository;
use Chamilo\Tests\ChamiloTestTrait;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \MessageTagRepository
 */
class MessageTagRepositoryTest extends WebTestCase
{
    use ChamiloTestTrait;

    public function testCreateTagAndDeleteUser(): void
    {
        self::bootKernel();
        $tagRepo = self::getContainer()->get(MessageTagRepository::class);

        $testUser = $this->createUser('test');

        // Create tag.
        $tag =
            (new MessageTag())
                ->setTag('my tag')
                ->setColor('red')
                ->setUser($testUser)
        ;

        $this->assertHasNoEntityViolations($tag);
        $tagRepo->update($tag);

        // One tag should be created.
        $count = $tagRepo->count([]);
        $this->assertSame(1, $count);

        // If I delete the user, then no tags.
        $userRepo = self::getContainer()->get(UserRepository::class);
        $userRepo->delete($testUser);

        $count = $tagRepo->count([]);
        $this->assertSame(0, $count);

        $this->assertSame(0, $tag->getPosition());
    }

    public function testCreateTags(): void
    {
        $tagRepo = self::getContainer()->get(MessageTagRepository::class);

        $testUser = $this->createUser('test');

        // Create first tag.
        $tag =
            (new MessageTag())
                ->setTag('tag 1')
                ->setUser($testUser)
        ;
        $this->assertHasNoEntityViolations($tag);
        $tagRepo->update($tag);
        $this->assertSame(0, $tag->getPosition());

        // Create second tag
        $tag2 =
            (new MessageTag())
                ->setTag('tag 2')
                ->setUser($testUser)
        ;
        $this->assertHasNoEntityViolations($tag2);
        $tagRepo->update($tag2);

        $count = $tagRepo->count([]);
        $this->assertSame(2, $count);

        $this->assertSame(1, $tag2->getPosition());
    }

    public function testCreateTagWithSameName(): void
    {
        $tagRepo = self::getContainer()->get(MessageTagRepository::class);

        $testUser = $this->createUser('test');

        // Create first tag.
        $tag =
            (new MessageTag())
                ->setTag('unique')
                ->setUser($testUser)
        ;
        $this->assertHasNoEntityViolations($tag);
        $tagRepo->update($tag);

        // Create second tag, with same name + same user
        $tag =
            (new MessageTag())
                ->setTag('unique')
                ->setUser($testUser)
        ;
        $violations = $this->getViolations($tag);
        $this->assertSame(1, $violations->count());

        $this->expectException(UniqueConstraintViolationException::class);
        $tagRepo->update($tag);

        $count = $tagRepo->count([]);
        $this->assertSame(1, $count);
    }
}