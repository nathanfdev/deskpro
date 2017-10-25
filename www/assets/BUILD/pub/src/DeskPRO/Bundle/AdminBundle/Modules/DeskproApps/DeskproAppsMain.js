
import { AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { AppServices } from 'DeskPRO/Bundle/AppsBundle/Modules/Services';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

import {
  registerIncomingWidgetRequestListeners,
  registerOutgoingWidgetRequestListeners
} from 'DeskPRO/Bundle/AppsBundle/Modules/WidgetMessage';

import * as postRobot from 'post-robot';

export class DeskproAppsMain {

  /**
   * @param {Window} windowObject
   * @param {AppsConfig} config
   */
  static bootstrapApps(windowObject, config)  {
    if (config.environment === 'production') {
      postRobot.CONFIG.LOG_LEVEL = 'error';
    }

    const appServices = new AppServices({ api, window: windowObject, config, apiToken: null });
    registerIncomingWidgetRequestListeners(appServices);
    registerOutgoingWidgetRequestListeners(appServices);
  }

  /**
   * Bootstraps application services
   *
   * @param {Window} windowObject
   */
  static main(windowObject)  {
    api.sendGet('DP_API/helpdesk/discover')
      .success((response) => {
        const config =  new AppsConfigBuilder().addWindowParams(windowObject)
          .addHelpdeskDiscoverySettings(response.data)
          .build()
        ;

        DeskproAppsMain.bootstrapApps(windowObject, config);
      });
  }
}
