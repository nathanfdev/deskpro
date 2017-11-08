import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import clone from 'lodash/clone';
import find from 'lodash/find';
import flattenDeep from 'lodash/flattenDeep';
import uniqueId from 'lodash/uniqueId';
import some from 'lodash/some';
import $ from 'jquery';
import { FormActionStore } from 'DeskPRO/Component/React/Standalone/FormActionStore';
import PortalSimpleSelectBox from './PortalSimpleSelectBox';

export class LevelSelectActionStore extends FormActionStore {

  onValueChanged(data) {
    const opts = this.el.find('option');
    opts.each((x, el) => {
      el.selected = some(data.value, v => parseInt(v, 10) === parseInt(el.value, 10));
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
    this.el.find('option, optgroup').each((x, optEl) => {
      const $optEl = $(optEl);
      let parent = $optEl.data('parent') || null;
      if (!parent || parent === '0' || parent === 0) {
        parent = null;
      }

      options.push({
        id:       $optEl.data('id'),
        title:    $optEl.data('title') || $optEl.data('name') || $optEl.text(),
        parent,
        children: []
      });
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

export class PortalMultipleSelectBox extends React.Component {

  static propTypes = {
    widgetOptions: PropTypes.object,
    actionStore:   PropTypes.object
  };

  constructor(props) {
    super(props);

    this.optionData = this.props.actionStore.getOptionData();
    this.state = {
      value: props.actionStore.getValue()
    };
  }

  componentDidMount() {
    const { actionStore } = this.props;
    const $el = actionStore.el;

    actionStore.on('formChanged', (data) => {
      this.setState({
        value: data.value
      });
    });

    $el.closest('form').on('reset', () => {
      actionStore.setValue([]);
    });
  }

  onClickOption = (options) => {
    const { actionStore } = this.props;
    actionStore.setValue(options ? options.map(opt => opt.id) : []);
  };

  renderSelect() {
    const { widgetOptions } = this.props;
    const mapOption = (g) => {
      const r = {
        id:       g.id,
        title:    g.title,
        children: g.children.map(mapOption),
        parent:   g.parent,
        depth:    g.path.length
      };

      if (r.children.length > 0) {
        return [r, r.children];
      }

      return r;
    };

    let options = this.optionData.hierarchy.map(mapOption);
    options = flattenDeep(options);

    const values = this.state.value.map(selectedId =>
      find(options, opt => parseInt(opt.id, 10) === parseInt(selectedId, 10))
    );

    return (
      <PortalSimpleSelectBox
        multiple
        widgetOptions={widgetOptions}
        options={options}
        value={values}
        level={1}
        onChange={this.onClickOption}
      />
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
 * @param {Object}             widgetOptions
 * @returns {LevelSelectActionStore}
 */
export function createComponent(select, renderTo, widgetOptions = {}) {
  const $select = $(select);
  $select.find('option, optgroup').each((x, opt) => {
    const $opt = $(opt);
    if (!$opt.data('id')) {
      $opt.data('id', $opt.data('id', uniqueId('opt_')));
    }
  });

  const actionStore = new LevelSelectActionStore($select);
  ReactDOM.render(React.createElement(PortalMultipleSelectBox, { actionStore, widgetOptions }), $(renderTo).get(0));

  return actionStore;
}
