import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import clone from 'lodash/clone';
import find from 'lodash/find';
import uniqueId from 'lodash/uniqueId';
import $ from 'jquery';
import { DropDownInput } from '@deskpro/portal-components';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { FormActionStore } from 'DeskPRO/Component/React/Standalone/FormActionStore';

export class LevelSelectActionStore extends FormActionStore {

  onValueChanged(data) {
    const opts = this.el.find('option');
    opts.each((x, el) => {
      el.selected = el.value === data.value || $(el).data('id') === data.value;
    });
    this.el.trigger('change');
  }

  readValueFromForm() {
    return this.el.find('option:selected').data('id') || null;
  }

  getOptionData() {
    const options = [];
    this.el.find('option').each((x, optEl) => {
      const $optEl = $(optEl);
      let parent = $optEl.data('parent') || null;
      if (!parent || parent === '0' || parent === 0) {
        parent = null;
      }

      const label = $optEl.data('title') || $optEl.data('name') || $optEl.text();

      if (label) {
        options.push({
          id:       $optEl.data('id'),
          value:    $optEl.data('id'),
          label:    $optEl.data('title') || $optEl.data('name') || $optEl.text(),
          parent,
          children: []
        });
      }
    });

    const walkerFn = (parent = null, path = []) => {
      const r = [];
      options.forEach((opt) => {
        if (opt.parent === parent) {
          opt.path = clone(path);
          path.push(opt.id);
          opt.children = walkerFn(opt.id, path);
          path.pop();

          r.push(opt);
        }
      });

      return r;
    };

    return { options, hierarchy: walkerFn() };
  }
}

export class HcSelectBox extends React.Component {

  static propTypes = {
    actionStore: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.updateOptions();

    const value = props.actionStore.getValue();
    const valuePath = this.getValuePath(value);

    this.state = {
      value,
      valuePath,
      expanded: false
    };
  }

  componentDidMount() {
    const { actionStore } = this.props;
    const $el = actionStore.el;

    actionStore.on('formChanged', (data) => {
      this.setState({
        value:     data.value,
        valuePath: this.getValuePath(data.value)
      });
    });

    $el.closest('form').on('reset', () => {
      actionStore.setValue(null);
    });
  }

  onClickOption = (value) => {
    this.props.actionStore.setValue(value);
  };

  getValuePath(value) {
    let path = [];
    if (!value) {
      return path;
    }

    const opt = find(this.optionData.options, o => o.id === value);
    if (opt) {
      path = clone(opt.path);
    } else {
      path = [];
    }

    path.push(value);

    return path;
  }

  updateOptions() {
    this.optionData = this.props.actionStore.getOptionData();
  }

  renderSelect(group) {
    return (
      <div>
        <DropDownInput
          dataSource={{ getOptions: group }}
          value={this.state.value}
          onChange={this.onClickOption}
          i18n={{ back: portalPhrases.get('helpcenter.general.back') }}
        />
      </div>
    );
  }

  render() {
    return this.renderSelect(this.optionData.hierarchy);
  }
}

/**
 * Configures and renders a multi-level select box bound to `select` into `renderTo`.
 *
 * @param {jQuery/HTMLElement} select
 * @param {jQuery/HTMLElement} renderTo
 * @param {Object}             widgetOptions
 * @returns {LevelSelectActionStore}
 */
export function createComponent(select, renderTo, widgetOptions = {}) {
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
  const component = React.createElement(HcSelectBox, { actionStore, widgetOptions });

  ReactDOM.render(component, $(renderTo).get(0));

  return actionStore;
}
