import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { loadAll, updateCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import $ from 'jquery';
import Immutable from 'immutable';

const setFormFields = contentTemplate => (page) => {
  const labels = [];
  const $fileList = $('ul.files.file-list', page.form);
  const setField = (field) => {
    const name = field.get('name');
    const value = field.get('value');

    if (name) {
      if (name.indexOf('[labels][]') !== -1) {
        labels.push(value);
        $('.article-tags input').val(labels.join(','));
      } else if (name.indexOf('[content]') !== -1 && page.rte.current) {
        page.rte.current.editor.current.reactEditor.current.editor.setContent(value);
      } else if (name.indexOf('[attach][]') !== -1) {
        const attachment = contentTemplate
          .get('attachments')
          .find(attach => Number(attach.get('blob').get('blob_id')) === Number(value));

        if (attachment) {
          const blob = attachment.get('blob');
          $fileList.append(`
          <li>
            <input type="hidden" name="${name}" value="${value}" />
            <em class="remove-attach-trigger"></em>
            <label>
              <a href="${blob.get('download_url')}"
                target="_blank"
                data-blob-id="${value}">
                ${blob.get('filename')}
              </a>
              <span>${blob.get('filesize_readable')}</span>
            </label>
          </li>
        `);
        }
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
      let categoriesSelect;
      let categoryId;
      if (name === 'newnews[brand]' && value !== page.meta.selectedBrandId) {
        categoryId = contentTemplate.get('template').find(a => a.get('name') === 'newnews[category_id]').get('value', '');
        $.ajax({
          url:     `${window.BASE_URL}agent/news/categories/brand/${value}?_rt=${window.DP_REQUEST_TOKEN}`,
          type:    'GET',
          context: this,
          success(result) {
            categoriesSelect = $(`#${page.meta.baseId}_cat`);
            categoriesSelect.children().remove();
            categoriesSelect.append($(result).find('option'));
            setTimeout(() => {
              categoriesSelect.val(`${categoryId}`).trigger('change');
            }, 200);
          }
        });
      }
      if (name === 'newarticle[brand]' && value !== page.meta.selectedBrandId) {
        categoryId = contentTemplate.get('template').find(a => a.get('name') === 'newarticle[category_id]').get('value', '');
        console.log(categoryId);
        $.ajax({
          url:     `${window.BASE_URL}agent/kb/article/categories/brand/${value}?_rt=${window.DP_REQUEST_TOKEN}`,
          type:    'GET',
          context: this,
          success(result) {
            categoriesSelect = $(`#${page.meta.baseId}_cat`);
            categoriesSelect.children().remove();
            categoriesSelect.append($(result).find('option'));
            setTimeout(() => categoriesSelect.val(categoryId).trigger('change'), 200);
          }
        });
      }
    } else if (Array.isArray(field.toJS())) {
      field.forEach(setField);
    }
  };

  contentTemplate.get('template').forEach(setField);

  $fileList.find('.remove-attach-trigger')
    .click(function () {
      $(this).parent('li').remove();
    });
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
    const type = contentTemplate.get('type');
    const dataLoader = setFormFields(contentTemplate);

    if (type === 'article') {
      window.DeskPRO_Window.newArticleLoader.open(dataLoader);
    } else if (type === 'news') {
      window.DeskPRO_Window.newNewsLoader.open(dataLoader);
    }
  }
);

export const openContentTemplateEditor = createAction(
  'AGENT_OPEN_CONTENT_TEMPLATE',
  (contentTemplate) => {
    const id = contentTemplate.get('id');
    const type = contentTemplate.get('type');
    const dataLoader = setFormFields(contentTemplate);

    if (type === 'article') {
      window.DeskPRO_Window.createEditArticleContentTemplateLoader(id).open(dataLoader);
    } else if (type === 'news') {
      window.DeskPRO_Window.createEditNewsContentTemplateLoader(id).open(dataLoader);
    }
  }
);
