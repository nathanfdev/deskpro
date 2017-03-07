import { createMemoryHistory } from 'react-router';
import { createBrowserHistory } from 'history';

const browserHistory = createBrowserHistory();

export const history = createMemoryHistory();
export const replaceRoute = (route) => {
  browserHistory.replace(route);
};
