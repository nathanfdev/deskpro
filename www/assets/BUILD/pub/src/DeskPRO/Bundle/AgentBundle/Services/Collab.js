class CollabWebSocketManagerSimple {
  constructor(apiUrl, jwt) {
    this.apiUrl = apiUrl;
    this.jwt = jwt;
    this.socket = undefined;
    this.listeners = [];

    setInterval(this.tick.bind(this), 500);
  }

  tick() {
    console.log('CollabWebSocketManagerSimple tick. Have listeners: ', this.listeners.length);
    const socket = this.socket;
    if (socket && socket.readyState === WebSocket.OPEN) {
      for (const handler of this.listeners) {
        handler(socket);
      }
    } else if (!socket || socket.readyState === WebSocket.CLOSED) {
      this.socket = new WebSocket(`${this.apiUrl}?authToken=${this.jwt}`);
    }
  }

  /**
   * Subscribe
   */
  onOnline(handler) {
    this.listeners.push(handler);
  }

  unsibscribe(handler) {
    let index = null;
    for (let i = 0; i < this.listeners.length; i++) if (this.listeners[i] === handler) index = i;

    if (index === null) {
      console.error('[CollabWebSocketManagerSimple] Failed to unsubscribe to event; listener for event was not found.');
      return;
    }

    this.listeners.splice(index, 1);
  }
}

export class CollabManager {

  // @TODO: avoid static/window. ...
  static getWebsocketManager() {
    console.log('getWebsocketManager', window.DP_COLLAB_WEBSOCKET_URL, window.DP_COLLAB_CONNECTION_TOKEN);
    console.log(CollabWebSocketManagerSimple);
    if (!window.DP_COLLAB_SOCKET_MANAGER) {
      window.DP_COLLAB_SOCKET_MANAGER = new CollabWebSocketManagerSimple(
        window.DP_COLLAB_WEBSOCKET_URL,
        window.DP_COLLAB_CONNECTION_TOKEN
      );
    }

    return window.DP_COLLAB_SOCKET_MANAGER;
  }
}
