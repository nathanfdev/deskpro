import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { loadAll, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import $ from 'jquery';
import Immutable from 'immutable';

const setFormFields = contentTemplate => (page) => {
  const labels = [];
  const setField = (field) => {
    const name = field.get('name');
    const value = field.get('value');

    if (name) {
      if (name === 'newarticle[labels][]') {
        labels.push(value);
        $('.article-tags input').val(labels.join(','));
      } else if (name === 'newarticle[content]' && page.rte.current) {
        page.rte.current.editor.current.reactEditor.current.editor.setContent(value);
      } else {
        const $el = $(page.form).find(`[name="${name}"]`);
        if ($el.is('textarea')) {
          if ($el.closest('.fr-box')) {
            $el.froalaEditor('html.set', value);
          } else {
            $el.html(value).trigger('change');
          }
          $el.val(value);
        } else if ($el.is(':checkbox, :radio')) {
          $el.each((i, v) => {
            $(v).attr('checked', value === $(v).val());
          });
        } else {
          $el.val(value).trigger('change');
        }
      }
    } else if (Array.isArray(field.toJS())) {
      field.forEach(setField);
    }
  };

  contentTemplate.get('template').forEach(setField);
};

export const loadContentTemplates = createAction(
  'AGENT_LOAD_CONTENT_TEMPLATES',
  (reload = false) => dispatch => dispatch(loadAll('ContentTemplate', reload))
);

export const editContentTemplate = createAction(
  'VAGENT_EDIT_CONTENT_TEMPLATE',
  (id, data) => dispatch => repository('ContentTemplate').update(data, id).success(() => {
    const contentTemplate = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('ContentTemplate', Immutable.List([contentTemplate]), 'merge'));
  })
);

export const deleteContentTemplate = createAction(
  'AGENT_DELETE_CONTENT_TEMPLATE',
  id => dispatch => repository('ContentTemplate').remove(id).success(() => {
    dispatch(removeFromCollection('ContentTemplate', 'all', [id]));
  })
);


export const openNewContentPage = createAction(
  'AGENT_OPEN_CONTENT_TEMPLATE',
  (contentTemplate) => {
    if (contentTemplate.get('type') === 'article') {
      window.DeskPRO_Window.newArticleLoader.open(setFormFields(contentTemplate));
    }
  }
);

export const openContentTemplateEditor = createAction(
  'AGENT_OPEN_CONTENT_TEMPLATE',
  (contentTemplate) => {
    if (contentTemplate.get('type') === 'article') {
      window.DeskPRO_Window.createEditArticleContentTemplateLoader(contentTemplate.get('id')).open(setFormFields(contentTemplate));
    }
  }
);
