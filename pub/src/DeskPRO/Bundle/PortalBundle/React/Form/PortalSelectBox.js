import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import FormActionStore from 'DeskPRO/Component/React/Standalone/FormActionStore';
import PortalSimpleSelectBox from './PortalSimpleSelectBox';
import _ from 'lodash';
import $ from 'jquery';

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

      options.push({
        id: $optEl.data('id'),
        title: $optEl.data('title') || $optEl.data('name') || $optEl.text(),
        parent: parent,
        children: []
      });
    });

    const walkerFn = (parent = null, path = []) => {
      const r = [];
      options.forEach(opt => {
        if (opt.parent === parent) {
          opt.path = _.clone(path);
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

export class PortalSelectBox extends React.Component {

  static propTypes = {
    widgetOptions: PropTypes.object,
    actionStore: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.actionStore = props.actionStore;
    this.updateOptions();

    const value = this.actionStore.getValue();
    const valuePath = this.getValuePath(value);
    this.state = {
      value: value,
      valuePath: valuePath,
      expanded: false
    };

    this.actionStore.on('formChanged', (data) => {
      this.setState({
        value: data.value,
        valuePath: this.getValuePath(data.value)
      });
    });
  }

  onClickOption(option) {
    this.actionStore.setValue(option.id);
  }

  getValuePath(value) {
    let path = [];
    if (!value) return path;

    const opt = _.find(this.optionData.options, o => o.id === value);
    if (opt) {
      path = _.clone(opt.path);
    } else {
      path = [];
    }

    path.push(value);

    return path;
  }

  updateOptions() {
    this.optionData = this.actionStore.getOptionData();
  }

  renderSelect(group, parentId = null, level = 1) {
    const { widgetOptions } = this.props;
    let subGroup = null;

    if (this.state.valuePath.length) {
      subGroup = _.find(group, i => this.state.valuePath.indexOf(i.id) !== -1);
    }

    const options = group.map((g) => {
      return {
        id: g.id,
        title: g.title
      };
    });

    return (
      <div>
        <PortalSimpleSelectBox
          widgetOptions={widgetOptions}
          options={options}
          value={subGroup ? subGroup : null}
          level={level}
          onChange={this.onClickOption.bind(this)} />

        {subGroup && subGroup.children.length ? this.renderSelect(subGroup.children, subGroup.id, level + 1) : null}
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
 * @param {FormActionStore}    actionStore
 * @param {Object}             widgetOptions
 * @returns {FormActionStore}
 */
export function createComponent(select, renderTo, actionStore = null, widgetOptions = {}) {
  const $select = $(select);

  // We need to rewrite opt-groups into normal options or else our widget
  // doesnt work :(
  $select.find('optgroup').each((x, optgroup) => {
    const $optgroup = $(optgroup);

    const memSel = $('<select>');
    const parentId = _.uniqueId('opt_');
    const newOpt = $('<option>');
    newOpt.data('id', parentId);
    newOpt.data('name', $optgroup.attr('label'));
    newOpt.attr('disabled', true);
    newOpt.text($optgroup.attr('label'));

    memSel.append(newOpt);

    $optgroup.find('option').each((i, opt) => {
      const $opt = $(opt).clone();
      $opt.data('parent', parentId);
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
      const newId = _.uniqueId('opt_');
      $opt.data('id', newId);
    }
  });

  if (!actionStore) {
    actionStore = new LevelSelectActionStore($select);
  }

  ReactDOM.render(React.createElement(PortalSelectBox, { actionStore, widgetOptions }), $(renderTo).get(0));

  return actionStore;
}
