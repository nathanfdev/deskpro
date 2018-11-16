<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Request;

use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\HttpKernel\Exception\PermanentRedirectException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRequestConverter implements ParamConverterInterface
{
    /**
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    private $brand_stack;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    public function __construct(BrandStack $brand_stack, PortalBrandThemeLoader $brand_theme_loader)
    {
        $this->brand_stack        = $brand_stack;
        $this->brand_theme_loader = $brand_theme_loader;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $param_name  = $configuration->getName();
        $param_class = $configuration->getClass();

        if ($request->attributes->get('tag_request')) { // already has one
            return;
        }

        if (!$tag_name = $request->get('_tag_name')) { // all tags must have this, if not it might not even be a tag request
            return;
        }

        $brand_theme = $this->brand_theme_loader->getPortalBrandTheme($this->brand_stack->getActive()->getBrand());
        $theme       = $brand_theme->getActiveTheme();
        $tag         = $theme->resolveTag($tag_name);

        $current_request = $request;
        $tag_options     = array_merge($tag->getDefaultOptions(), $current_request->query->get('tag_options', []));
        $query           = array_merge($current_request->query->all(), ['tag_options' => $tag_options]);

        $attrs = array_merge($current_request->attributes->all(), ['_tag_name' => $tag_name]);

        $tag_request = new TagRequest($query, [], $attrs);
        $tag_request->attributes->set('_controller', $tag->getControllerName());
        $tag_request->setOptionsResolver(new OptionsResolver());
        if ($session = $request->getSession()) {
            $tag_request->setSession($session);
        }

        $request->attributes->set('tag_request', $tag_request);

        //$current_request = $this->container->get('request_stack')->getCurrentRequest();
        //$query = array('tag_options' => array_merge($tag->getDefaultOptions(), $arguments));
        //$attrs = array_merge($current_request->attributes->all(), array('_tag_name' => $tag_name));
        //$tag_request = new TagRequest($query, array(), $attrs);
        //$attrs['tag_request'] = $tag_request;
        //$tag_request->attributes->set('tag_request', $tag_request);
        //unset($attrs['_cache']);
        //$param_options    = $this->getOptions($configuration);
        //$slug_attribute_name = $param_options['slug_route_param'];
        //$slug_route_param = $request->attributes->get($slug_attribute_name);
        //$slug_col         = $param_options['slug_col'];

        //$repo = $this->em->getRepository($param_class);
        //if ($obj = $repo->findOneBy(array($slug_col => $slug_route_param))) {
        //    $request->attributes->set($param_name, $obj);

        //    return;
        //}

        //$id = substr($slug_route_param, 0, strpos($slug_route_param, '-'));
        //if ($obj = $repo->find($id)) {
        //    // it exists and we are on the old url at the moment, lets flag a 301 response
        //    $new_slug_attribute = array(
        //        $slug_attribute_name => $obj->slug
        //    );

        //    throw new PermanentRedirectException(
        //        $request->attributes->get('_route'),
        //        array_merge(
        //            $request->attributes->get('_route_params'),
        //            $new_slug_attribute
        //        )
        //    );
        //}

        //throw new NotFoundHttpException(sprintf('could not find a "%s" for the slug value found in the route variable "%s" (value: %s)', $param_class, $slug_attribute_name, $slug_route_param));
    }

    public function supports(ParamConverter $configuration)
    {
        return 'tag_request' === $configuration->getName();
    }
}
