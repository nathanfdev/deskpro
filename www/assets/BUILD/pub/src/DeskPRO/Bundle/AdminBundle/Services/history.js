import { createMemoryHistory } from 'react-router';
import { createHashHistory } from 'history';

const hashHistory = createHashHistory();

export const history = createMemoryHistory();
export const replaceRoute = (route) => {
  hashHistory.replace(route);
};
