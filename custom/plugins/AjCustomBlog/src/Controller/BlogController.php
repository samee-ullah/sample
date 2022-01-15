<?php

namespace AjCustomBlog\Controller;

use Sas\BlogModule\Content\Blog\BlogEntriesEntity;
use Sas\BlogModule\Controller\BlogController as BlogControllerParent;
use Sas\BlogModule\Page\Search\BlogSearchPageLoader;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cache\Annotation\HttpCache;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class BlogController extends BlogControllerParent
{
    private GenericPageLoaderInterface $genericPageLoader;
    private SalesChannelCmsPageLoaderInterface $cmsPageLoader;
    private SystemConfigService $systemConfigService;
    private EntityRepositoryInterface $blogRepository;
    private BlogSearchPageLoader $blogSearchPageLoader;
    private EntityRepositoryInterface $productRepository;
    private EntityRepositoryInterface $blogAuthorRepository;

    public function __construct(
        SystemConfigService                $systemConfigService,
        GenericPageLoaderInterface         $genericPageLoader,
        SalesChannelCmsPageLoaderInterface $cmsPageLoader,
        EntityRepositoryInterface          $blogRepository,
        BlogSearchPageLoader               $blogSearchPageLoader,
        EntityRepositoryInterface          $productRepository,
        EntityRepositoryInterface          $blogAuthorRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->genericPageLoader = $genericPageLoader;
        $this->cmsPageLoader = $cmsPageLoader;
        $this->blogRepository = $blogRepository;
        $this->blogSearchPageLoader = $blogSearchPageLoader;
        $this->productRepository = $productRepository;
        $this->blogAuthorRepository = $blogAuthorRepository;
    }

    /**
     * @HttpCache()
     * @Route("/sas_blog/{articleId}", name="sas.frontend.blog.detail", methods={"GET"})
     */
    public function detailAction(string $articleId, Request $request, SalesChannelContext $context): Response
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page = NavigationPage::createFrom($page);

        $criteria = new Criteria([$articleId]);

        $criteria->addAssociations(['author.salutation', 'blogCategories']);

        $results = $this->blogRepository->search($criteria, $context->getContext())->getEntities();

        /** @var BlogEntriesEntity $entry */
        $entry = $results->first();

        if (!$entry) {
            throw new PageNotFoundException($articleId);
        }

        $pages = $this->cmsPageLoader->load(
            $request,
            new Criteria([$this->systemConfigService->get('SasBlogModule.config.cmsBlogDetailPage')]),
            $context
        );

        $page->setCmsPage($pages->first());
        $metaInformation = $page->getMetaInformation();

        $metaInformation->setAuthor($entry->getAuthor()->getTranslated()['name']);

        $page->setMetaInformation($metaInformation);

        $page->setNavigationId($page->getHeader()->getNavigation()->getActive()->getId());

        // Customizations to get blog product
        $product = $this->getBlogProduct($entry, $context->getContext());
        $entry->addExtension('product', $product);

        return $this->renderStorefront('@Storefront/storefront/page/content/index.html.twig', [
            'page' => $page,
            'entry' => $entry,
        ]);
    }

    public function getBlogProduct($entry, $context)
    {
        $productId = $entry->get('customFields')['custom_blog_sections_product'] ?? null;
        $product = $productId;

        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('media');
        if ($productId) {
            $product = $this->productRepository->search($criteria, $context)->getEntities()->first();
        }

        return $product;
    }

    /**
     * @HttpCache()
     * @RouteScope(scopes={"storefront"})
     * @Route("/blog-author/{authorId}", name="aj.frontend.blogAuthor.detail", methods={"GET"})
     */
    public function getAuthorDetails(string $authorId, Request $request, SalesChannelContext $context): Response
    {
        $criteria = new Criteria([$authorId]);
        $criteria->addAssociation('blogs');

        $author = $this->blogAuthorRepository->search($criteria, $context->getContext())->getEntities()->first();

        $page = $this->genericPageLoader->load($request, $context);
        return $this->renderStorefront('@AjCustomBlog/storefront/page/author/detail.html.twig', [
            'page' => $page,
            'author' => $author
        ]);
    }
}
