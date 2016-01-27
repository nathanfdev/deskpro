import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import FormActionStore from 'DeskPRO/Component/React/Standalone/FormActionStore';
import { PortalSimpleSelectBox } from './PortalSimpleSelectBox';
import _ from 'lodash';
import $ from 'jquery';

export class LevelSelectActionStore extends FormActionStore {

  onValueChanged(data) {
    const opts = this.el.find('option');
    opts.each((x, el) => {
      el.selected = _.some(data.value, (v) => {
        return _.parseInt(v) === _.parseInt(el.value);
      });
    });
    this.el.trigger('change');
  }

  readValueFromForm() {
    const v = [];
    this.el.find('option:selected').each((x, opt) => {
      const $opt = $(opt);
      v.push($opt.data('id'));
    });

    return v;
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

export class PortalMultipleSelectBox extends React.Component {

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

  onClickOption = options => {
    this.actionStore.setValue(options ? options.map((opt) => opt.id) : []);
  };

  getValuePath(value) {
    let path = [];
    if (!value) {
      return path;
    }

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

  renderSelect() {
    const { widgetOptions } = this.props;
    const mapOption = (g) => {
      const r = {
        id: g.id,
        title: g.title,
        children: g.children.map(mapOption),
        parent: g.parent,
        depth: g.path.length
      };

      if (r.children.length > 0) {
        return [r, r.children];
      }

      return r;
    };

    let options = this.optionData.hierarchy.map(mapOption);
    options = _.flattenDeep(options);
    const values = this.actionStore.getValue().map(selectedId => {
      return _.find(options, (opt) => _.parseInt(opt.id) === _.parseInt(selectedId));
    });

    return (
      <PortalSimpleSelectBox
        multiple
        widgetOptions={widgetOptions}
        options={options}
        value={values}
        level={1}
        onChange={this.onClickOption} />
    );
  }

  render() {
    return this.renderSelect();
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
  $select.find('option').each((x, opt) => {
    const $opt = $(opt);
    if (!$opt.data('id')) {
      $opt.data('id', $opt.data('id', _.uniqueId('opt_')));
    }
  });

  if (!actionStore) {
    actionStore = new LevelSelectActionStore($select);
  }

  ReactDOM.render(React.createElement(PortalMultipleSelectBox, { actionStore, widgetOptions }), $(renderTo).get(0));

  return actionStore;
}
