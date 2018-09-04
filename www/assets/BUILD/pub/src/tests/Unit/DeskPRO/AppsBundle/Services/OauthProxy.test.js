import { OauthProxy } from 'DeskPRO/Bundle/AppsBundle/Modules/Services/OauthProxy';
import { AppsConfig } from 'DeskPRO/Bundle/AppsBundle/Modules/Config/AppsConfig';

describe('oauth proxy generates oauth urls', () => {
  const appsConfig = new AppsConfig({
    environment:        'production',
    oauthProxyEndpoint: 'https://deskpro-dev/api/v2/apps/proxy-oauth',
    httpProxyEndpoint:  'https://deskpro-dev/api/v2/apps/proxy-http',
    helpdeskUrl:        'https://deskpro-dev',
    helpdeskBuild:      '1',
    apiEndpoint:        'https://deskpro-dev/api/v2/apps',
    apiRoot:            'http://deskpro-dev/api/v2'
  });

  const authParams = {
    applicationId:  1,
    correlationId:  10,
    callbackMethod: 'postMessage',
    callbackUrl:    'https://deskpro-dev/page-url'
  };

  it('buildAuthorizeUrl should generate an oauth2.0 url by default', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });

    const expectedUrl = 'https://deskpro-dev/api/v2/apps/proxy-oauth/jira/authorize?dp_state=eyJjb3JyZWxhdGlvbklkIjoxMH0%3D&dp_callbackMethod=postMessage&dp_callbackUrl=https%3A%2F%2Fdeskpro-dev%2Fpage-url&dp_applicationId=1';

    const defaultUrl = proxy.buildAuthorizeUrl({  provider: 'jira' })(authParams);
    const oauth2Url = proxy.buildAuthorizeUrl({  provider: 'jira', protocolVersion: '2.0' })(authParams);

    expect(defaultUrl).toEqual(expectedUrl);
    expect(oauth2Url).toEqual(expectedUrl);
  });

  it('buildAuthorizeUrl should generate an oauth1.0 url by default', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });

    const expectedUrl = 'https://deskpro-dev/api/v2/apps/proxy-oauth/1.0/jira/authorize?dp_callbackMethod=postMessage&dp_callbackUrl=https%3A%2F%2Fdeskpro-dev%2Fpage-url&dp_applicationId=1';

    const oauth2Url = proxy.buildAuthorizeUrl({  provider: 'jira', protocolVersion: '1.0' })(authParams);
    expect(oauth2Url).toEqual(expectedUrl);
  });

  it('buildAuthorizeUrl should generate an oauth1.0 url by default', () => {
    const proxy = new OauthProxy({  appsConfig, username: 'joe', apiToken: 'token' });

    const expectedUrl = 'https://deskpro-dev/api/v2/apps/proxy-oauth/1.0a/jira/authorize?dp_callbackMethod=postMessage&dp_callbackUrl=https%3A%2F%2Fdeskpro-dev%2Fpage-url&dp_applicationId=1';

    const oauth2Url = proxy.buildAuthorizeUrl({  provider: 'jira', protocolVersion: '1.0a' })(authParams);
    expect(oauth2Url).toEqual(expectedUrl);
  });

  it('buildAuthorizeUrl should throw an error when protocol version unknown', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });
    expect(() => proxy.buildAuthorizeUrl({  provider: 'jira', protocolVersion: '3.0a' })).toThrow();
  });

  it('buildRefreshAccessUrl should throw an error when protocol version unknown', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });
    expect(() => proxy.buildRefreshAccessUrl({  provider: 'jira', protocolVersion: '3.0a' })).toThrow();
  });

  it('getWidgetSettings should generate an oauth2.0 url by default', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });

    const expectedUrl = 'https://deskpro-dev/api/v2/apps/proxy-oauth/jira/grant-access/1';

    const { urlRedirect: defaultUrl }   = proxy.getWidgetSettings({  provider: 'jira', }, { applicationId: 1 });
    const { urlRedirect:oauth2Url } = proxy.getWidgetSettings({  provider: 'jira', protocolVersion: '2.0' }, { applicationId: 1 });

    expect(defaultUrl).toEqual(expectedUrl);
    expect(oauth2Url).toEqual(expectedUrl);
  });

  it('getWidgetSettings should generate an oauth 1.0 url ', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });

    const { urlRedirect: actualUrl } = proxy.getWidgetSettings({  provider: 'jira', protocolVersion: '1.0' }, { applicationId: 1 });
    expect(actualUrl).toEqual('https://deskpro-dev/api/v2/apps/proxy-oauth/1.0/jira/grant-access/1');
  });

  it('getWidgetSettings should generate an oauth 1.0a url ', () => {
    const proxy = new OauthProxy({ appsConfig, username: 'joe', apiToken: 'token' });

    const { urlRedirect: actualUrl } = proxy.getWidgetSettings({  provider: 'jira', protocolVersion: '1.0a' }, { applicationId: 1 });
    expect(actualUrl).toEqual('https://deskpro-dev/api/v2/apps/proxy-oauth/1.0a/jira/grant-access/1');
  });
});
