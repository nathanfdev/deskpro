import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import uniqueId from 'lodash/uniqueId';
import { PageWidget } from 'DeskPRO/Component/PageWidget/PageWidget';
import { DropDown } from '@deskpro/portal-components';
import { LevelSelectActionStore } from '../../../../React/Form/PortalSelectBox';

/**
 * Configures and renders a multi-level select box bound to `select` into `renderTo`.
 *
 * @param {jQuery/HTMLElement} select
 * @param {jQuery/HTMLElement} renderTo
 * @param {Object}             widgetOptions
 * @returns {LevelSelectActionStore}
 */
function createComponent(select, renderTo, widgetOptions = {}) {
  const $select = $(select);

  // We need to rewrite opt-groups into normal options or else our widget
  // doesnt work :(

  // remember value to set it again after the modifications
  // because we loose it if selectbox is changed
  const value = $select.val();

  $select.find('optgroup').each((x, optgroup) => {
    const $optgroup = $(optgroup);

    const memSel = $('<select>');
    const parentId = uniqueId('opt_');
    const newOpt = $('<option>');
    newOpt.attr('data-id', parentId);
    newOpt.attr('data-name', $optgroup.attr('label'));
    newOpt.attr('disabled', true);
    newOpt.text($optgroup.attr('label'));

    memSel.append(newOpt);

    $optgroup.find('option').each((i, opt) => {
      const $opt = $(opt).clone();
      $opt.attr('data-id', uniqueId('opt_'));
      $opt.attr('data-parent', parentId);
      memSel.append($opt);
    });

    $optgroup.after(memSel.children());
    $optgroup.remove();
  });

  // make sure each option has a unique id
  // for use within the react widget
  $select.find('option').each((x, opt) => {
    const $opt = $(opt);
    if (!$opt.data('id')) {
      const newId = uniqueId('opt_');
      $opt.data('id', newId);
    }
  });

  $select.val(value);

  const actionStore = new LevelSelectActionStore($select);
  const component = React.createElement(DropDown, { actionStore, widgetOptions });

  ReactDOM.render(component, $(renderTo).get(0));

  return actionStore;
}

export class HelpcenterSelectBox extends PageWidget {

  renderWidget() {
    this.$element.hide();
    this.$rElement = $('<div class="dp-react-widget as-dpui"></div>').insertAfter(this.$element);
    this.actionStore = createComponent(this.$element, this.$rElement, this.options);
  }
}

