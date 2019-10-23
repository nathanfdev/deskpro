import { CollabWebSocketManagerSimple } from '@deskpro/content-editor';

export class CollabManager {

  // @TODO: avoid static/window. ...
  static getWebsocketManager() {
    if (!window.DP_COLLAB_SOCKET_MANAGER) {
      window.DP_COLLAB_SOCKET_MANAGER = new CollabWebSocketManagerSimple(
        window.DP_COLLAB_WEBSOCKET_URL,
        window.DP_COLLAB_CONNECTION_TOKEN
      );
    }

    return window.DP_COLLAB_SOCKET_MANAGER;
  }
}
