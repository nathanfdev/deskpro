import { registerIncomingRequestListeners, receiveMessage, receiveSubscription } from './receiver';
import { registerOutgoingMessageListener, createEmitAsync } from './emitter';
import bindIncomingMessageHandlers from './IncomingRequestHandlers';
import setTimeoutImplementation from './setTimeout';

// handlers

export { bindIncomingMessageHandlers, registerIncomingRequestListeners, registerOutgoingMessageListener };

// message receivers

export { receiveMessage, receiveSubscription };

/**
 * A function that distributes the message to all listening app widgets
 *
 * @type {function(string, *): Promise}
 */
export const emitAsync = createEmitAsync(setTimeoutImplementation());

