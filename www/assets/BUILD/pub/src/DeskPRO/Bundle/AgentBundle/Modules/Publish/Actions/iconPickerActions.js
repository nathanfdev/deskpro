import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadIcons = createAction(
  'PUBLISH_ICON_PICKER_LOAD_CUSTOM_ICONS',
  () => new Promise((resolve) => {
    repository('CustomIcons').loadAll().then((promise) => {
      const icons = promise.getData().data;
      icons.forEach((icon) => {
        icon.tags = icon.tags.filter(t => t !== 'custom_icon');
      });
      resolve({ icons });
    });
  })
);
