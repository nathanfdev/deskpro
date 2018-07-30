import { createAction } from 'DeskPRO/Component/Ampliflux';

export const changeSection = createAction(
  'SIDEBAR_CHANGE_SECTION',
  params => params
);
export default changeSection;
