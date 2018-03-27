<?php

namespace spec\DeskPRO\Bundle\ApiBundle\Proxy;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProxyRequestFactorySpec.
 *
 * @mixin \DeskPRO\Bundle\ApiBundle\Proxy\ProxyRequestFactory
 */
class ProxyRequestFactorySpec extends ObjectBehavior
{
    public function let(
        EntityManager      $em,
        AppStateRepository $appStateRepository,
        Request            $request,
        ParameterBag       $headers
    ) {
        $this->beConstructedWith($appStateRepository);
        $request->headers = $headers;

        $headers->get('X-Proxy-SignWith', null)->willReturn(null);
        $headers->get('X-Proxy-Url')->willReturn('http://deskpro.dev/');
        $headers->get('X-Proxy-Method')->willReturn('POST');
        $headers->get('X-Proxy-AutoHeaders', 'true')->willReturn('true');
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(false);
        $headers->all()->willReturn([]);
    }

    public function it_returns_proxy_method(AppInstance $instance, Request $request, Person $person)
    {
        $request->getMethod()->shouldNotBeCalled();

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyMethod()->shouldReturn('POST');
    }

    public function it_returns_original_request_method(
        AppInstance  $instance,
        Request      $request,
        Person       $person,
        ParameterBag $headers
    ) {
        $headers->get('X-Proxy-Method')->willReturn(null);
        $request->getMethod()->willReturn('PUT');

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyMethod()->shouldReturn('PUT');
    }

    public function it_should_return_proxy_url_as_is(
        AppInstance  $instance,
        Request      $request,
        Person       $person,
        ParameterBag $headers
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(null);
        $headers->get('X-Proxy-Url')->willReturn('{{settings.site_url}}/api/some-endpoint');

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyUrl()->shouldReturn('{{settings.site_url}}/api/some-endpoint');
    }

    public function it_should_replace_proxy_url_var_to_undefined(
        AppInstance  $instance,
        Request      $request,
        Person       $person,
        ParameterBag $headers
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(true);
        $headers->get('X-Proxy-Url')->willReturn('{{settings.site_url}}/api/some-endpoint');

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyUrl()->shouldReturn('(undefined)/api/some-endpoint');
    }

    public function it_should_replace_proxy_url_setting_vars(
        AppInstance        $instance,
        Request            $request,
        Person             $person,
        ParameterBag       $headers,
        AppStateRepository $appStateRepository
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(true);
        $headers->get('X-Proxy-Url')->willReturn('{{settings.site_url}}/api/some-endpoint');
        $instance->getSettings()->willReturn([
            'site_url' => 'http://deskpro-dev',
        ]);
        $instance->getApp()->willReturn(null);
        $appStateRepository->findReadableByName($instance, $person, [])->willReturn([]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyUrl()->shouldReturn('http://deskpro-dev/api/some-endpoint');
    }

    public function it_should_replace_proxy_url_app_state_vars(
        AppInstance        $instance,
        Request            $request,
        Person             $person,
        ParameterBag       $headers,
        AppStateRepository $appStateRepository
    ) {
        $appState1 = new AppState();
        $appState1->setName('site_url');
        $appState1->setValue('http://deskpro-dev');

        $appState2 = new AppState();
        $appState2->setName('endpoint');
        $appState2->setValue('some-endpoint');

        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(true);
        $headers->get('X-Proxy-Url')->willReturn('{{privateState.site_url}}/api/{{privateState.endpoint}}');
        $instance->getSettings()->willReturn([]);
        $instance->getApp()->willReturn(null);
        $appStateRepository->findReadableByName($instance, $person, ['site_url', 'endpoint'])->willReturn([
            $appState1,
            $appState2,
        ]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyUrl()->shouldReturn('http://deskpro-dev/api/some-endpoint');
    }

    public function it_skips_app_state_vars_by_scope(
        AppInstance        $instance,
        Request            $request,
        Person             $person,
        ParameterBag       $headers,
        AppStateRepository $appStateRepository
    ) {
        $appState = new AppState();
        $appState->setName('site_url');
        $appState->setValue('http://deskpro-dev');

        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(true);
        $headers->get('X-Proxy-Url')->willReturn('{{privateState.site_url}}/api/some-endpoint');
        $instance->getSettings()->willReturn([]);
        $instance->getApp()->willReturn(null);
        $appStateRepository->findReadableByName($instance, $person, ['site_url'])->willReturn([]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyUrl()->shouldReturn('(undefined)/api/some-endpoint');
    }

    public function it_should_return_while_list_as_is(
        AppInstance  $instance,
        Request      $request,
        Person       $person,
        App          $app,
        AppManifest  $manifest
    ) {
        $whiteList = [
            'http://my_url/api/*',
            'http://{{settings.site_url}}/api/*',
        ];

        $instance->getApp()->willReturn($app);
        $app->getManifest()->willReturn($manifest);
        $manifest->getExternalApis()->willReturn($whiteList);
        $instance->getSettings()->willReturn([
            'site_url' => 'http://deskpro-dev',
        ]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getWhiteList()->shouldReturn($whiteList);
    }

    public function it_should_return_white_list_with_replaced_vars(
        AppInstance        $instance,
        Request            $request,
        Person             $person,
        App                $app,
        AppManifest        $manifest,
        ParameterBag       $headers,
        AppStateRepository $appStateRepository
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(true);
        $instance->getApp()->willReturn($app);
        $instance->getSettings()->willReturn([
            'site_url' => 'deskpro-dev',
        ]);

        $appStateRepository->findReadableByName($instance, $person, [])->willReturn([]);

        $app->getManifest()->willReturn($manifest);
        $manifest->getExternalApis()->willReturn([
            'http://my_url/api/*',
            'http://{{settings.site_url}}/api/*',
        ]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getWhiteList()->shouldReturn([
            'http://my_url/api/*',
            'http://deskpro-dev/api/*',
        ]);
    }

    public function it_should_return_proxy_headers_as_is(
        AppInstance  $instance,
        Request      $request,
        Person       $person,
        ParameterBag $headers
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(null);
        $headers->get('X-Proxy-AutoHeaders', 'true')->willReturn('false');
        $headers->all()->willReturn([
            'header-1'                     => 'value 1',
            'x-proxy-header-authorization' => 'value 2',
            'x-proxy-header-person-id'     => 'value 3',
        ]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyHeaders()->shouldReturn([
            'authorization' => 'value 2',
            'person-id'     => 'value 3',
        ]);
    }

    public function it_should_return_proxy_headers_with_replaced_vars(
        AppInstance        $instance,
        Request            $request,
        Person             $person,
        ParameterBag       $headers,
        AppStateRepository $appStateRepository
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(true);
        $headers->get('X-Proxy-AutoHeaders', 'true')->willReturn('false');
        $headers->all()->willReturn([
            'header-1'                     => 'value 1',
            'x-proxy-header-authorization' => [
                'key {{settings.api_key}}',
            ],
        ]);

        $instance->getApp()->willReturn(null);
        $instance->getSettings()->willReturn([
            'api_key' => 'key_val',
        ]);

        $appStateRepository->findReadableByName($instance, $person, [])->willReturn([]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyHeaders()->shouldReturn([
            'authorization' => [
                'key key_val',
            ],
        ]);
    }

    public function it_should_collect_auto_headers(
        AppInstance  $instance,
        Request      $request,
        Person       $person,
        ParameterBag $headers
    ) {
        $headers->get('X-Proxy-ReplaceVars', false)->willReturn(null);
        $headers->get('X-Proxy-AutoHeaders', true)->willReturn(true);
        $headers->all()->willReturn([
            'header-1'                     => 'value 1',
            'x-proxy-header-authorization' => 'value 2',
            'x-proxy-header-person-id'     => 'value 3',
            'x-forward-url'                => 'http://deskpro-dev',
        ]);

        $proxyRequest = $this->createFromAppRequest($instance, $request, $person);
        $proxyRequest->getProxyHeaders()->shouldReturn([
            'header-1'      => 'value 1',
            'authorization' => 'value 2',
            'person-id'     => 'value 3',
        ]);
    }
}
