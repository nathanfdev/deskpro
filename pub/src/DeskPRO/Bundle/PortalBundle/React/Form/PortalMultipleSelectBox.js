import _ from "lodash";
import $ from "jquery";
import FormActionStore from "DeskPRO/Component/React/Standalone/FormActionStore";
import React from "react";
import ReactDOM from 'react-dom';
import PortalSimpleSelectBox from "./PortalSimpleSelectBox";

//######################################################################################################################
//# Action Store
//######################################################################################################################

export class LevelSelectActionStore extends FormActionStore {
  onValueChanged(data) {
    let opts = this.el.find('option');
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
      opt = $(opt);
      v.push(opt.data('id'));
    });
    return v;
  }

  getOptionData() {
    let options = [];
    this.el.find('option').each((x, optEl) => {
      optEl = $(optEl);
      let parent = optEl.data('parent') || null;
      if (!parent || parent === "0" || parent === 0) {
        parent = null;
      }

      options.push({
        id: optEl.data('id'),
        title: optEl.data('title') || optEl.data('name') || optEl.text(),
        parent: parent,
        children: []
      });
    });

    let walkerFn = (parent = null, path = []) => {
      let r = [];

      options.forEach(opt => {
        if (opt.parent == parent) {
          opt.path = _.clone(path);
          path.push(opt.id);
          opt.children = walkerFn(opt.id, path);
          path.pop();

          r.push(opt);
        }
      });

      return r;
    };

    let hierarchy = walkerFn();

    return {
      options: options,
      hierarchy: hierarchy
    }
  }
}

//######################################################################################################################
//# React Component
//######################################################################################################################

export class PortalMultipleSelectBox extends React.Component {
  constructor(props) {
    super(props);
    this.actionStore = this.props.actionStore;
    this.updateOptions();

    let value = this.actionStore.getValue();
    let valuePath = this.getValuePath(value);
    this.state = {
      value: value,
      valuePath: valuePath,
      expanded: false
    };

    this.actionStore.on('formChanged', (data) => {
      let value = data.value;
      let valuePath = this.getValuePath(value);
      let state = {value: value, valuePath: valuePath};
      this.setState(state);
    });
  }

  updateOptions() {
    this.optionData = this.actionStore.getOptionData();
  }

  getValuePath(value) {
    let path = [];
    if (!value) return path;

    let opt = _.find(this.optionData.options, o => o.id == value);
    if (opt) {
      path = _.clone(opt.path);
    } else {
      path = [];
    }

    path.push(value);

    return path;
  }

  getValueForId(id) {
    return _.find(this.optionData.options, o => o.id == id);
  }

  onClickOption(options) {
    this.actionStore.setValue(options ? options.map((opt) => {
      return opt.id;
    }) : []);
  }

  renderSelect() {
    const map_option = (g) => {
      const r = {
        id: g.id,
        title: g.title,
        children: g.children.map(map_option),
        parent: g.parent,
        depth: g.path.length
      };

      if (r.children.length > 0) {
        return [
            r,
            r.children
        ]
      } else {
        return r;
      }
    };
    let options = this.optionData.hierarchy.map(map_option);
    options = _.flattenDeep(options);
    const values = this.actionStore.getValue().map((selected_id) => {
      return _.find(options, (opt) => {
        return _.parseInt(opt.id) === _.parseInt(selected_id);
      });
    });

    return (
      <div>
        <PortalSimpleSelectBox multiple="true" options={options} value={values} level="1" onChange={this.onClickOption.bind(this)} />
      </div>
    );
  }

  render() {
    return this.renderSelect();
  }
}

//######################################################################################################################
//# Factory
//######################################################################################################################

/**
 * Configures and renders a multi-level select box bound to `select` into `renderTo`.
 *
 * @param {jQuery/HTMLElement} select
 * @param {jQuery/HTMLElement} renderTo
 * @param {FormActionStore}    actionStore
 * @returns {FormActionStore}
 */
export function createComponent(select, renderTo, actionStore = null) {
  select = $(select);
  select.find('option').each((x, opt) => {
    opt = $(opt);
    if (!opt.data('id')) {
      opt.data('id', opt.data('id', _.uniqueId('opt_')));
    }
  });

  if (!actionStore) {
    actionStore = new LevelSelectActionStore(select);
  }

  ReactDOM.render(React.createElement(PortalMultipleSelectBox, {actionStore: actionStore}), $(renderTo).get(0));

  return actionStore;
}
