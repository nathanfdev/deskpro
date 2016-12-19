import { createMemoryHistory } from 'react-router';

export const history = createMemoryHistory();
export const getLocation = callback => history.listen(callback)();
