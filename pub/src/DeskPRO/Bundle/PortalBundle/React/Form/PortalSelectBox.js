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

class SelectOption extends React.Component {
  onClickOption(e) {
    e.preventDefault();
    this.props.onClickOption(this.props.option);
  }
  render() {
    const option = this.props.option;
    return (
      <li>
        <a onClick={this.onClickOption.bind(this)} className={this.props.active ? 'active' : null}>{option.title}</a>
      </li>
    );
  }
}

export class LevelSelect2 extends React.Component {
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

  getValueForId(id) {
    return _.find(this.optionData.options, o => o.id == id);
  }

  componentDidMount() {
    document.addEventListener("click", this.documentClickHandler.bind(this));
  }

  componentWillUnmount() {
    document.removeEventListener("click", this.documentClickHandler.bind(this));
  }

  documentClickHandler() {
    this.setState({
      expanded: false
    });
  }

  dropdownClickHandler(e) {
    e.nativeEvent.stopImmediatePropagation();
  }

  onClickOption(option) {
    console.log(option);
    this.setState({
      value: option.id,
      valuePath: option.id,
      expanded: false
    });
    this.actionStore.setValue(option.id);
  }

  onClickTopOption() {
    this.setState({
      expanded: !this.state.expanded
    });
  }

  renderSelect(group, parentId = null) {
    let subGroup = null;

    if (this.state.valuePath.length) {
      subGroup = _.find(group, i => this.state.valuePath.indexOf(i.id) !== -1);
    }

    return (
      <div className="multiselect">
        {this.renderTopOption()}
          {this.renderDropdownList(group)}
          {/**subGroup && subGroup.children.length ? this.renderSelect(subGroup.children, subGroup.id) : null*/}
      </div>
    );
  }


  renderTopOption(){
   if (this.state.value && !this.state.expanded) {
     return (<div className="default" onClick={this.onClickTopOption.bind(this)}>
       <span>{this.getValueForId(this.state.value).title}</span><i className="fa fa-caret-down"></i>
     </div>);
   } else {
     return (<div className="default" onClick={this.onClickTopOption.bind(this)}><span>Select...</span><i className="fa fa-caret-down"></i>
     </div>);
   }
  }

  renderDropdownList(group) {
    if (!this.state.expanded) {
      return null;
    }

    return (
      <ul className="first-level"> {group.map((o) => {
        return (
          <SelectOption onClickOption={this.onClickOption.bind(this)}
                        key={o.id}
                        option={o}
                        active={o.id == this.state.value}/>
        );
      })}
      </ul>);
  }

  render() {
    return (
      <div className="dp-level-select" onClick={this.dropdownClickHandler.bind(this)}>
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

  ReactDOM.render(React.createElement(LevelSelect2, {actionStore: actionStore}), $(renderTo).get(0));

  return actionStore;
}
