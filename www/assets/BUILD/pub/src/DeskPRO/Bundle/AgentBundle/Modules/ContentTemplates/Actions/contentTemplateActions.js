import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import $ from 'jquery';

export const loadContentTemplates = createAction(
  'AGENT_LOAD_CONTENT_TEMPLATES',
  (reload = false) => dispatch => dispatch(loadAll('ContentTemplate', reload))
);

export const openContentTemplate = createAction(
  'AGENT_OPEN_CONTENT_TEMPLATE',
  (contentTemplate) => {
    const setFormFields = (page) => {
      contentTemplate.get('template').forEach((field) => {
        const $el = $(page.form).find(`[name="${field.get('name')}"]`);
        if ($el.is('textarea')) {
          if ($el.closest('.fr-box')) {
            $el.froalaEditor('html.set', field.get('value'));
          } else {
            $el.html(field.get('value')).trigger('change');
          }
        } else if ($el.is(':checkbox, :radio')) {
          $el.each((i, v) => {
            $(v).attr('checked', field.get('value') === $(v).val());
          });
        } else {
          $el.val(field.get('value')).trigger('change');
        }
      });
    };

    if (contentTemplate.get('type') === 'article') {
      window.DeskPRO_Window.newArticleLoader.open(setFormFields);
    }
  }
);
