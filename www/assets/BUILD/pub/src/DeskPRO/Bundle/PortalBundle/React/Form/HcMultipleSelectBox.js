import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import clone from 'lodash/clone';
import uniqueId from 'lodash/uniqueId';
import some from 'lodash/some';
import $ from 'jquery';
import { MultipleDropDownInput } from '@deskpro/portal-components';
import { FormActionStore } from 'DeskPRO/Component/React/Standalone/FormActionStore';

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

export class PortalMultipleSelectBox extends React.Component {

  static propTypes = {
    actionStore: PropTypes.object
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

  onClickOption = (values) => {
    const { actionStore } = this.props;
    actionStore.setValue(values);
  };

  renderSelect() {
    const options = this.optionData.hierarchy;

    return (
      <MultipleDropDownInput
        dataSource={{ getOptions: options }}
        value={this.state.value}
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
