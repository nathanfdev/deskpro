
import { AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { AppServices } from 'DeskPRO/Bundle/AppsBundle/Modules/Services';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

import {
  registerIncomingWidgetRequestListeners,
  registerOutgoingWidgetRequestListeners
} from 'DeskPRO/Bundle/AppsBundle/Modules/WidgetMessage';

export class DeskproAppsMain {

  /**
   * Bootstraps application services
   *
   * @param {Window} windowObject
   */
  static main(windowObject)  {
    const builder = new AppsConfigBuilder();
    builder.addWindowParams(windowObject);
    const config = builder.build();

    const appServices = new AppServices({ api, window: windowObject, config, apiToken: null });
    registerIncomingWidgetRequestListeners(appServices);
    registerOutgoingWidgetRequestListeners(appServices);
  }
}
