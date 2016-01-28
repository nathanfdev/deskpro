import _ from "lodash";
import $ from "jquery";
import FormActionStore from "DeskPRO/Component/React/Standalone/FormActionStore";
import React from "react";
import ReactDOM from 'react-dom';

//######################################################################################################################
//# Action Store
//######################################################################################################################

export class LevelSelectActionStore extends FormActionStore {
  onValueChanged(data) {
    let opts = this.el.find('option');
    opts.each((x, el) => {
      el.selected = el.value == data.value;
    });
    this.el.trigger('change');
  }

  readValueFromForm() {
    return this.el.find('option:selected').data('id') || null;
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
        id:       optEl.data('id'),
        title:    optEl.data('title') || optEl.data('name') || optEl.text(),
        parent:   parent,
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

export class LevelSelect extends React.Component {
  constructor(props) {
    super(props);
    this.actionStore = this.props.actionStore;
    this.updateOptions();

    let value = this.actionStore.getValue();
    let valuePath = this.getValuePath(value);
    this.state = { value: value, valuePath: valuePath };

    this.actionStore.on('formChanged', (data) => {
      let value = data.value;
      let valuePath = this.getValuePath(value);
      let state = { value: value, valuePath: valuePath };
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

  handleSelectChange(event) {
    let sel = $(event.target);
    let opt = sel.find('option:selected');
    let value = null;

    // Set value to new option
    if (opt.data('id')) {
      value = opt.data('id');

    } else {
      // Set value to the last option selected
      if (sel.data('parent') && sel.data('parent') !== 0 && sel.data('parent') !== "0") {
        value = sel.data('parent');
      }
    }

    let valuePath = this.getValuePath(value);
    this.setState({ value: value, valuePath: valuePath });
    this.actionStore.setValue(value);
  }

  renderSelect(group, parentId = null) {
    let subGroup = null;

    if (this.state.valuePath.length) {
      subGroup = _.find(group, i => this.state.valuePath.indexOf(i.id) !== -1);
    }

    return (
      <div className="level deskpro-choice-widget">
        <span className="level-indent"><i></i></span>
        <div className="select-wrap">
          <select data-parent={parentId} defaultValue={subGroup ? subGroup.id : null} onChange={this.handleSelectChange.bind(this)}>
            <option value="0"></option>
            {group.map(function(o) {
              return <option key={o.id} data-id={o.id} value={o.id}>{o.title}</option>;
            })}
          </select>
        </div>
          {subGroup && subGroup.children.length ? this.renderSelect(subGroup.children, subGroup.id) : null}
      </div>
    );
  }

  render() {
    return (
      <div className="dp-level-select">
          {this.renderSelect(this.optionData.hierarchy)}
      </div>
    );
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

  ReactDOM.render(React.createElement(LevelSelect, {actionStore: actionStore}), $(renderTo).get(0));

  return actionStore;
}
