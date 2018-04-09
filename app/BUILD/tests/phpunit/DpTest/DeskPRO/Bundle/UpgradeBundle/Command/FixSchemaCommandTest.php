<?php

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Command;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\UpdateBundle\Command\FixSchemaCommand;
use Doctrine\DBAL\Connection;
use DpTest\AbstractKernelAwareTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FixSchemaCommandTest.
 */
class FixSchemaCommandTest extends AbstractKernelAwareTestCase
{
    /**
     * @var FixSchemaCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $connection->executeQuery('DELETE FROM article_categories');
        $connection->executeQuery('DELETE FROM articles');
        $connection->executeQuery('DELETE FROM people');

        $application = new Application($this->getContainer()->get('kernel'));
        $application->add(new FixSchemaCommand());

        $this->command = $application->find('dp:update:fix-schema');
        $this->command->setApplication($application);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainer()
    {
        return $this->getApiKernel()->getContainer();
    }

    public function test_integrity_all_fine()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command'             => $this->command->getName(),
            '--fix-ref-integrity' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Checking integrity. This may take a while.', $output);
    }

    public function test_integrity_preview()
    {
        $category1 = new ArticleCategory();
        $category1->setTitle('category 1');
        $this->getEntityManager()->persist($category1);

        $category2 = new ArticleCategory();
        $category2->setTitle('category 2');
        $this->getEntityManager()->persist($category2);

        $article1 = new Article();
        $article1->setTitle('my article 1');
        $article1->addToCategory($category1);

        $article2 = new Article();
        $article2->setTitle('my article 2');
        $article2->addToCategory($category2);

        $this->getEntityManager()->persist($article1);
        $this->getEntityManager()->persist($article2);
        $this->getEntityManager()->flush();

        $connection = $this->createConnection();
        $connection->executeQuery('DELETE FROM article_categories WHERE id = :category_id', [
            'category_id' => $category1->getId(),
        ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command'             => $this->command->getName(),
            '--fix-ref-integrity' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains("DELETE FROM article_to_categories WHERE (article_id = '{$article1->getId()}') AND (category_id = '{$category1->getId()}')", $output);
        $this->assertNotContains("DELETE FROM article_to_categories WHERE (article_id = '{$article2->getId()}') AND (category_id = '{$category2->getId()}')", $output);
    }

    public function test_integrity_set_null()
    {
        $category1 = new ArticleCategory();
        $category1->setTitle('parent category');
        $this->getEntityManager()->persist($category1);

        $category2 = new ArticleCategory();
        $category2->setTitle('child category');
        $category2->setParent($category1);
        $this->getEntityManager()->persist($category2);

        $this->getEntityManager()->persist($category1);
        $this->getEntityManager()->flush();

        $connection = $this->createConnection();
        $connection->executeQuery('DELETE FROM article_categories WHERE id = :category_id', [
            'category_id' => $category1->getId(),
        ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command'             => $this->command->getName(),
            '--fix-ref-integrity' => true,
            '--run'               => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains("UPDATE article_categories SET parent_id = NULL WHERE id = '{$category2->getId()}'", $output);

        $this->getEntityManager()->clear();
        $category2 = $this->getEntityManager()->getRepository(ArticleCategory::class)->find($category2->getId());
        $this->assertNull($category2->getParent());
    }

    public function test_integrity_delete_cascade()
    {
        $person = new Person();
        $person->setName('My Name');

        $email = new PersonEmail();
        $email->setEmail('my@example.com');
        $person->addEmail($email);

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();

        $connection = $this->createConnection();
        $connection->executeQuery('DELETE FROM people WHERE id = :person_id', [
            'person_id' => $person->getId(),
        ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command'             => $this->command->getName(),
            '--fix-ref-integrity' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains("DELETE FROM people_emails WHERE id = '{$email->getId()}'", $output);
    }

    /**
     * @return Connection
     */
    private function createConnection()
    {
        $params     = $this->getContainer()->get('deskpro.db_config_reader')->getParams('default');
        $connection = $this->getContainer()->get('doctrine.dbal.connection_factory')->createConnection($params);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');

        return $connection;
    }
}
