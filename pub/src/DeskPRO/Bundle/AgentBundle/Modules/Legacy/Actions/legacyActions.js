import { createAction } from 'Ampliflux';

export const loadRoute = createAction('LEGACY_LOAD_ROUTE', (path, type = 'page') => ({ type, path}));
