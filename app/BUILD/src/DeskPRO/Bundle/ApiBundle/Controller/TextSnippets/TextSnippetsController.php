<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\TextSnippets;

use Application\DeskPRO\Entity\TextSnippet;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TextSnippetContent;
use DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet\TextSnippetType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TextSnippetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/{context}_snippets", requirements={"context"="(ticket|chat)"})
 * @ApiDoc(
 *     target="all",
 *     section="Text snippets",
 *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\TextSnippets\TextSnippet"
 * )
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet\TextSnippetType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TextSnippet",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "type"="tickets"
 *      }
 *     }
 * )
 */
class TextSnippetsController extends AbstractTextSnippetsController
{
    public static $entity    = TextSnippet::class;
    public static $type      = TextSnippetType::class;
    public static $listOrder = 'asc';

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(array_merge($params, $masterRequest->query->all()), null, [
            '_controller' => 'ApiBundle:TextSnippets\TextSnippets:list',
            'context'     => $masterRequest->attributes->get('context'),
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @ApiDoc(
     *      description="get the snippets content",
     *      requirements={
     *          {
     *              "name"="snippet",
     *              "requirement"="ticket|chat",
     *              "description"="the context of the category",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the snippet",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="We can't find such snippet or context is wrong"
     *      },
     *     output="array<Application\DeskPRO\Entity\TextSnippetContent>"
     * )
     *
     * @Rest\Get("/{id}/content")
     *
     * @param Request $request
     * @param int     $id
     * @param string  $context
     *
     * @return TextSnippetContent[]
     */
    public function getContentAction(Request $request, $id, $context)
    {
        $entity   = $this->findEntity($id, $request);
        $contents = $entity->getTextSnippetContents();

        // if related entity id was provided then we need to process the text replacements
        $contextEntityId = $request->query->getInt($context);
        if ($contextEntityId) {
            $templateRenderer        = $this->get('twig_template_renderer');
            $contextParams[$context] = $this->findOr404($this->getContextEntityClass($request), $contextEntityId)->toApiData();

            foreach ($contents as $content) {
                $content
                    ->setTitle($templateRenderer->renderStringTemplate($content->getTitle(), $contextParams))
                    ->setContent($templateRenderer->renderStringTemplate($content->getContent(), $contextParams))
                ;
            }
        }

        return $this->wrap($contents ?: new \ArrayObject());
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb
            ->join('e.category', 'c')
            ->andWhere('c.typename = :category_type')
            ->andWhere('c.person = :user_id OR c.is_global = true')
            ->setParameter('category_type', $this->getSnippetTypeName($request))
            ->setParameter('user_id', $this->getUser()->getId())
        ;

        $query = $request->query;

        // filter by person
        if ($query->get('my')) {
            $qb->andWhere('e.person = :user_id');
        } elseif ($query->get('global')) {
            $qb->andWhere('e.person is null');
        } else {
            $qb->andWhere('e.person = :user_id OR e.person is null');
        }

        // filter by category
        if ($query->get('category')) {
            $qb->andWhere('e.category = :category_id');
            $qb->setParameter('category_id', $request->get('category'));
        }

        // filter by draft
        if ($query->has('draft')) {
            $qb->andWhere('e.is_draft = :is_draft');
            $qb->setParameter('is_draft', $query->getInt('draft'));
        }

        $this->applyFilterByLanguageAndQueryString($request, $qb, 'text_snippets');
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'type'   => $this->getSnippetTypeName($request),
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        /** @var TextSnippet $entity */
        $entity   = parent::findEntity($id, $request);
        $category = $entity->getCategory();

        if ($category) {
            if ($category->getTypename() !== $this->getSnippetTypeName($request)) {
                throw $this->createNotFoundException();
            }
            if (!$category->getIsGlobal() && $category->getPerson() !== $this->getUser()) {
                throw $this->createNotFoundException();
            }
        } else {
            throw $this->createNotFoundException();
        }
        if ($entity->getPerson() && $entity->getPerson() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLocationUrl($entity, Request $request, array $params = [])
    {
        return parent::getLocationUrl($entity, $request, [
            'context' => $request->attributes->get('context'),
        ]);
    }
}
