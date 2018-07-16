import { EVENT_CONTEXT_PROPERTY_GET } from 'DeskPRO/Bundle/AppsBundle/Modules/WidgetMessage/IncomingRequestHandlers';

describe('EVENT_CONTEXT_PROPERTY_GET throws errors', () => {
  const existingTabId = 'tab-5';

  function createTab(apiv2Data) {
    return {
      page: { meta: { api_v2_data: apiv2Data } }
    };
  }

  function createServices({ getTab })  {
    return { tabs: { getTab } };
  }

  function createWidgetMessage({ tabId, path })  {
    return { body: { tabId, path } };
  }

  function createGetTab(apiv2Data)  {
    return function (tabId) {
      return tabId === existingTabId ? createTab(apiv2Data) : null;
    };
  }

  it('EVENT_CONTEXT_PROPERTY_GET should return error when tab is not found', () => {
    let error = null;
    let data = null;
    const apiv2Data = {};

    const response = function (err, d) { error = err; data = d; };
    const services = createServices({
      getTab: createGetTab(apiv2Data)
    });
    const messageBody = { tabId: 'tab-25', path: ['a', 'property'] };
    const widgetMessage = createWidgetMessage(messageBody);
    EVENT_CONTEXT_PROPERTY_GET(response, {}, widgetMessage, services);
    expect(data).toMatchObject(messageBody);
    expect(error).toBeInstanceOf(Error);
  });

  it('EVENT_CONTEXT_PROPERTY_GET return error when property is not found', () => {
    let error = null;
    let data = null;
    const apiv2Data = {};

    const response = function (err, d) { error = err; data = d; };
    const services = createServices({
      getTab: createGetTab(apiv2Data)
    });
    const messageBody = { tabId: existingTabId, path: ['a', 'property'] };
    const widgetMessage = createWidgetMessage(messageBody);

    EVENT_CONTEXT_PROPERTY_GET(response, {}, widgetMessage, services);

    expect(error).toBeInstanceOf(Error);
    expect(data).toMatchObject(messageBody);
  });
});


describe('EVENT_CONTEXT_PROPERTY_GET returns expected value', () => {
  function createServices({ getTab })  {
    return { tabs: { getTab } };
  }

  function createWidgetMessage({ tabId, path })  {
    return { body: { tabId, path } };
  }

  function createGetTab(apiv2Data)  {
    return function () {
      return {
        page: { meta: { api_v2_data: apiv2Data } }
      };
    };
  }

  it('EVENT_CONTEXT_PROPERTY_GET returns entire object when path is empty', () => {
    let error = null;
    let data = null;
    const apiv2Data = {
      joe: 'belushi'
    };

    const response = function (err, d) { error = err; data = d; };
    const services = createServices({
      getTab: createGetTab(apiv2Data)
    });
    const widgetMessage = createWidgetMessage({
      tabId: 'tab-25',
      path:  []
    });
    EVENT_CONTEXT_PROPERTY_GET(response, {}, widgetMessage, services);

    expect(error).toBe(null);
    expect(data).toMatchObject(apiv2Data);
  });

  it('EVENT_CONTEXT_PROPERTY_GET returns specific property', () => {
    let error = null;
    let data = null;
    const apiv2Data = {
      joe: {
        anna: 'belushi'
      }
    };

    const response = function (err, d) { error = err; data = d; };
    const services = createServices({
      getTab: createGetTab(apiv2Data)
    });
    const widgetMessage = createWidgetMessage({
      tabId: 'tab-25',
      path:  ['joe', 'anna']
    });
    EVENT_CONTEXT_PROPERTY_GET(response, {}, widgetMessage, services);

    expect(error).toBe(null);
    expect(data).toEqual('belushi');
  });
});
