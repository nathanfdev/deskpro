import { default as serializeError } from 'serialize-error';

let nextMessageId = 0;
// this function is necessary because eslint rules complain about using unary operators :(
const incrementMessageId = () => {
  nextMessageId += 1;
  return nextMessageId;
};

// this function is necessary because eslint rules complain about using unary operators :(
let nextCorrelationId = 0;
const incrementCorrelationId = () => {
  nextCorrelationId += 1;
  return nextCorrelationId;
};


export class WidgetRequest {
  /**
   * @param body
   * @param id
   * @param correlationId
   * @param widgetId
   */
  constructor({ body, id, correlationId, widgetId })  {
    this.props = { body, id, correlationId, widgetId };
  }

  get id() { return this.props.id; }

  get widgetId() { return this.props.widgetId; }

  get correlationId() { return this.props.correlationId; }

  get body() { return this.props.body; }

  toJS = () => ({ ...this.props })
}

export class WidgetResponse {
  /**
   * @param id
   * @param widgetId
   * @param correlationId
   * @param {*} body
   * @param status
   */
  constructor({ id, widgetId, correlationId, body, status })  {
    this.props = { id, widgetId, correlationId, body, status };
  }

  get id() { return this.props.id; }

  get widgetId() { return this.props.widgetId; }

  get correlationId() { return this.props.correlationId; }

  get status() { return this.props.status; }

  get body() { return this.props.body; }

  toJS = () => ({ ...this.props })
}

/**
 * @param {WidgetRequest} widgetRequest
 * @param {Error|String} error
 * @param {Object} data additional error data
 * @return WidgetResponse
 */
export const createErrorResponse = (widgetRequest, error, data) => {
  const id = incrementMessageId();
  const { widgetId, correlationId } = widgetRequest;
  let body = null;
  if (error instanceof Error || typeof error === 'object') {
    body = JSON.parse(JSON.stringify(serializeError(error)));
    body = JSON.stringify({ ...body, errorData: data });
  } else {
    body = JSON.stringify({ message: error, errorData: data });
  }

  return new WidgetResponse({ id, widgetId, correlationId, body, status: 'error' });
};

/**
 * @param {WidgetRequest} widgetRequest
 * @param data
 *
 * @return WidgetResponse
 */
export const createSuccessResponse = (widgetRequest, data) => {
  const id = incrementMessageId();
  const { widgetId, correlationId } = widgetRequest;
  const body = JSON.stringify(data);

  return new WidgetResponse({ id, widgetId, correlationId, body, status: 'success' });
};

/**
 * @param {Widget} widget
 * @param {*} body
 * @return {WidgetRequest}
 */
export const createRequest = (widget, body) => {
  const id = incrementMessageId();
  const correlationId = incrementCorrelationId();
  const { instanceId: widgetId } = widget;

  return new WidgetRequest({ id, correlationId, widgetId, body });
};

/**
 * @param widgetMessage
 * @return {WidgetRequest|WidgetResponse}
 */
export const parseIncomingMessageJS = (widgetMessage) => {
  const { args, messageId, status, body, correlationId, id, widgetId } = widgetMessage;
  if (status) {
    const parsedBody = status === 'error' && typeof  body === 'string' ? JSON.parse(body) : body;
    return new WidgetResponse({ id, widgetId, correlationId, body: parsedBody, status });
  }

  if (args) {
    return new WidgetRequest({ id: messageId, widgetId, correlationId, body: args[0] });
  }

  if (correlationId) {
    return new WidgetRequest({ id, correlationId, widgetId, body });
  }

  return null;
};
