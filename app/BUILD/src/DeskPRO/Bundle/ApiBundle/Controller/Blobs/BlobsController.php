<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Blobs;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\AcceptAttachmentType;
use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Component\Pagerfanta\LimitedPager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\ContentTypes;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlobsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/blobs")
 * @ApiDoc(target="all", section="Blobs", output="Application\DeskPRO\Entity\Blob")
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType"
 *     }
 * )
 */
class BlobsController extends CrudController
{
    public static $exposeOnly = ['list', 'get', 'post', 'delete'];
    public static $entity     = Blob::class;
    public static $type       = BlobAuthType::class;

    /**
     * @Rest\Post("/temp")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postTempAction(Request $request)
    {
        $form = $this->createForm(AcceptAttachmentType::class, null, [
            'upload_context' => 'agent',
            'required'       => true,
            'with_context'   => true,
        ]);
        $form->submit($this->getRequestData($request));

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        return View::create($this->wrap($form->getData()), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Post("/form_data")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postFormDataAction(Request $request)
    {
        if ($request->request->has('file') && $request->request->has('name')) {
            $dataUri  = $request->request->get('file');
            $mimeType = 'application/octet-stream';
            if (preg_match('|data:([^;]*);|', $dataUri, $matches)) {
                $mimeType = $matches[1];
            }
            $binary = file_get_contents($dataUri);
            $name   = $request->request->get('name');
            if ($name === 'undefined' && preg_match('|^image/(.*)|', $mimeType, $matches)) {
                $name = 'image.'.$matches[1];
            }
            $blob = $this->get('blob.storage')->createBlobRecordFromString(
                $binary,
                $name,
                $mimeType
            );

            return View::create($this->wrap($blob), Response::HTTP_CREATED);
        }
        throw $this->createBadRequestException();
    }

    /**
     * @Rest\Post("/froala")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postFroalaAction(Request $request)
    {
        $file   = $request->files->get('file');
        $accept = $this->getContainer()->getAttachmentAccepter();
        $blob   = $accept->accept($file);

        $return['link'] = $blob->getDownloadUrl(true);

        return new Response(json_encode($return), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Post("/load_remote_images")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postLoadRemoteImagesAction(Request $request)
    {
        $fs = new Filesystem();

        $images    = $request->get('images');
        $tmpDir    = $this->get('deskpro.app_env')->getUserTmpDir();
        $fileId    = uniqid('remote_images', true);
        $tmpFolder = $tmpDir.DIRECTORY_SEPARATOR.'remote_images'.DIRECTORY_SEPARATOR.$fileId.DIRECTORY_SEPARATOR;
        mkdir($tmpFolder, 0777, true);
        foreach ($images as &$image) {
            $filename = basename($image['source']);
            $mimeType = ContentTypes::getContentTypeFromFilename($filename);
            if (!$mimeType) {
                $mimeType = ContentTypes::getContentTypeFromDataUrl($image['source']);
            }

            file_put_contents($tmpFolder.$filename, fopen($image['source'], 'r'));
            $blob = $this->get('blob.storage')->createBlobRecordFromFile(
                $tmpFolder.$filename,
                $filename,
                $mimeType
            );
            $image['blob'] = $blob;
        }
        $fs->remove($tmpFolder);

        return View::create($this->wrap($images), Response::HTTP_CREATED);
    }

    /**
     * Get resource with provided id.
     *
     * @ApiDoc(
     *      description="Get a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="authId",
     *              "requirement"="(\d+\-)?[A-Z0-9]+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{authId}", requirements={"authId"="(\d+\-)?[A-Z0-9]+"})
     *
     * @param Request $request
     * @param int     $authId
     *
     * @return View
     */
    public function getAction(Request $request, $authId)
    {
        $this->denyAccessUnlessGranted(
            PermissionGroupVoter::VIEW,
            $this->getPermissionGroupEntityContext($authId, $request)
        );

        return View::create($this->wrap($this->findEntity($authId, $request)), Response::HTTP_OK);
    }

    /**
     * Entities list.
     *
     * Selects entities based on the provided "ids" parameter or returns paginated list of no IDs provided.
     * Look carefully at filters section to have a great filtering, grouping or sorting power
     *
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="authIds", "pattern"="[(\d+\-)?[A-Z0-9]+,]+", "description"="Comma separated list of AuthIDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $qb = $this->getManager()->createQueryBuilder();
        $qb->select('e');
        $qb->from(static::$entity, 'e');

        $this->applyListFilters($qb, 'e', $request);
        $this->applySorting($qb, 'e', $request);

        $authIds = $request->get('ids');
        if ($authIds) {
            if (is_string($authIds)) {
                $authIds = explode(',', $authIds);
            }

            $authIds = array_map(
                function ($authId) {
                    return preg_replace('|^(\d*\-)?|', '', $authId);
                },
                $authIds
            );
            if (count($authIds) > static::$listMaxResults) {
                throw $this->createBadRequestException(
                    'You can select maximum '.static::$listMaxResults.' entities'
                );
            }

            $qb->andWhere('e.authcode IN (:authIds)');
            $qb->setParameters(compact('authIds'));
        } else {
            throw $this->createBadRequestException('Blobs can\'t be listed without authIds');
        }

        $limit = (int) $request->query->getInt('limit', static::$listLimit);
        if ($limit && $limit < 0) {
            throw $this->createBadRequestException('You must select a limit of at least 1');
        }

        // return QueryBuilder result or Pagerfanta depending on if pagination is enabled for the controller
        if (static::$listPaginate) {
            $page  = (int) $request->query->getInt('page', 1);
            $count = (int) $request->query->getInt('count', static::$listPerPage);

            if ($count > static::$listMaxResults) {
                throw $this->createBadRequestException(
                    'You can select maximum '.static::$listMaxResults.' entities'
                );
            } elseif ($count <= 0) {
                throw $this->createBadRequestException('You must select at least 1 entity');
            }

            if ($limit) {
                // adding limit to the initial qb will
                // make the initial COUNT have a limit, which
                // might speed it up a bit
                $qb->setMaxResults($limit);

                $pagerAdapter = new DoctrineORMAdapter($qb);
                $pager        = new LimitedPager($pagerAdapter, $limit);
            } else {
                $pagerAdapter = new DoctrineORMAdapter($qb);
                $pager        = new Pagerfanta($pagerAdapter);
            }

            $pager->setMaxPerPage($count);
            $pager->setCurrentPage($page);

            $result = $pager;
        } else {
            if ($limit) {
                $qb->setMaxResults($limit);
            }
            $result = $qb->getQuery()->getResult();
        }

        return View::create($this->wrap($result), Response::HTTP_OK);
    }

    /**
     * Obviously it's an ability to erase what you've done.
     * Be careful there is no CTRL+Z shortcut.
     *
     * @ApiDoc(
     *      description="Delete a blob",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="authId",
     *              "requirement"="(\d+\-)?[A-Z0-9]+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such blob anymore",
     *          404="Well, looks like either blob already deleted either it doesn't exists at all"
     *      }
     * )
     * @ApiModes({"key"})
     * @ApiUserContext("admin")
     * @Rest\Delete("/{authId}", requirements={"authId"="(\d+\-)?[A-Z0-9]+"})
     *
     * @param int     $authId
     * @param Request $request
     *
     * @return View
     */
    public function deleteAction($authId, Request $request)
    {
        $this->denyAccessUnlessGranted(
            PermissionGroupVoter::DELETE,
            $this->getPermissionGroupEntityContext($authId, $request)
        );

        $entity = $this->findEntity($authId, $request);
        $this->deleteEntity($entity);

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @param string $class
     * @param int    $authId
     * @param string $message
     *
     * @return object
     */
    protected function findOr404($class, $authId, $message = null)
    {
        if (!$entity = $this->getManager()->getRepository(Blob::class)->getByAuthId($authId)) {
            $message or $message = "#{$authId} Not Found";
            throw $this->createNotFoundException($message);
        }

        return $entity;
    }

    /**
     * @ApiDoc(
     *     description="See archive info",
     *     requirements={
     *          {
     *              "name"="authId",
     *              "requirement"="(\d+\-)?[A-Z0-9]+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *     output="DeskPRO\Bundle\AppBundle\Archive\Archive"
     * )
     * @Rest\Get("/{authId}/archive", requirements={"authId"="(\d+\-)?[A-Z0-9]+"})
     *
     * @param int     $authId
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getArchiveInfoAction($authId, Request $request)
    {
        /** @var Blob $blob */
        $blob    = $this->findEntity($authId, $request);
        $archive = $this->getArchive($blob);

        try {
            $zip = $this->get('archive_factory')->createZipArchive();
            $zip->open($archive);
        } finally {
            @unlink($archive);
        }

        return new View($this->wrap($zip->getInfo()));
    }

    /**
     * @ApiDoc(
     *     description="See archive content",
     *     requirements={
     *         {
     *             "name"="authId",
     *             "requirement"="(\d+\-)?[A-Z0-9]+",
     *             "description"="The id of the resource",
     *             "dataType"="integer"
     *         }
     *     },
     *     output="array"
     * )
     * @Rest\Get("/{authId}/files", requirements={"authId"="(\d+\-)?[A-Z0-9]+"})
     *
     * @param int     $authId
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getArchiveFilesAction($authId, Request $request)
    {
        if (!Blob::hasZipArchiveClass()) {
            return new View(null, Response::HTTP_NOT_IMPLEMENTED);
        }

        /** @var Blob $blob */
        $blob = $this->findEntity($authId, $request);

        $archive = $this->getArchive($blob);

        try {
            $zip = $this->get('archive_factory')->createZipArchive();
            $zip->open($archive);
            $content = $zip->getMembers();
        } finally {
            @unlink($archive);
        }

        return new View($this->wrap($content));
    }

    /**
     * @ApiDoc(
     *     description="Serve archived content",
     *     requirements={
     *         {
     *             "name"="authId",
     *             "requirement"="(\d+\-)?[A-Z0-9]+",
     *             "description"="The id of the resource",
     *             "dataType"="integer"
     *         },
     *         {
     *             "name"="path",
     *             "description"="The path of the file",
     *         }
     *     },
     *     output="string"
     * )
     * @Rest\Get("/{authId}/download/{path}", requirements={"authId"="(\d+\-)?[A-Z0-9]+","path"=".+"})
     *
     * @param int     $authId
     * @param string  $path
     * @param Request $request
     *
     * @throws \Exception
     */
    public function getArchiveExtractedFileAction($authId, $path, Request $request)
    {
        /** @var Blob $blob */
        $blob = $this->findEntity($authId, $request);

        $archive = $this->getArchive($blob);

        $found = false;
        try {
            $zip = $this->get('archive_factory')->createZipArchive();
            $zip->open($archive);

            $files = $zip->getMembers();
            foreach ($files as $file) {
                if ($file['name'] === $path) {
                    if ($file['size'] < 100000000) {
                        $content = $zip->extractMembers($path);
                        $found   = true;
                    } else {
                        throw new \Exception('Compressed file is too big');
                    }
                    break;
                }
            }
            if (!$found) {
                throw new \Exception('File not found in archive');
            }
        } finally {
            @unlink($archive);
        }

        $pieces = explode('/', $path);

        $filename = array_pop($pieces);
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Disposition: attachment; filename="'.addslashes($filename).'"');

        readfile($content[$path]);

        unset($zip);
        exit;
    }

    /**
     * @param Blob $blob
     *
     * @return string
     */
    private function getArchive(Blob $blob)
    {
        /** @var DeskproBlobStorage $blobStorage */
        $blobStorage = $this->get('deskpro.blob_storage');
        $fileId      = uniqid('archive', true);
        $tmpDir      = $this->get('deskpro.app_env')->getUserTmpDir();
        $archive     = $tmpDir.'/'.$fileId.$blob->getFilename();

        $blobStorage->copyBlobRecordToFile($archive, $blob);

        return $archive;
    }
}
