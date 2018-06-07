import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const startImport = createAction(
  'ADMIN_APPS_IMPORTER_START_IMPORT',
  data => api.sendPost('DP_API/importer/start_import', data)
);

export const stopImport = createAction(
  'ADMIN_APPS_IMPORTER_STOP_IMPORT',
  data => api.sendPost('DP_API/importer/stop_import', data)
);

export const testSettings = createAction(
  'ADMIN_APPS_IMPORTER_TEST_SETTINGS',
  data => api.sendPost('DP_API/importer/test_settings', data)
);

export const getImportStatus = createAction(
  'ADMIN_APPS_IMPORTER_GET_STATUS',
  (id) => {
    if (id) {
      return api.sendGet(`DP_API/importer/status/${id}`);
    }

    return api.sendGet('DP_API/importer/status');
  }
);
