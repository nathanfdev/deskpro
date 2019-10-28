import { CollabWebSocketConnectionManagerSimple } from '@deskpro/content-editor';

export class CollabManager {

  // @TODO: avoid static/window. ...
  static getConnectionManager() {
    if (!window.DP_COLLAB_SOCKET_MANAGER) {
      window.DP_COLLAB_SOCKET_MANAGER = new CollabWebSocketConnectionManagerSimple(
        window.DP_COLLAB_WEBSOCKET_URL,
        window.DP_COLLAB_CONNECTION_TOKEN
      );
    }

    return window.DP_COLLAB_SOCKET_MANAGER;
  }
}
