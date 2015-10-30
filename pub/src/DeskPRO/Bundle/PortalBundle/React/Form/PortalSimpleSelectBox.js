import _ from "lodash";
import $ from "jquery";
import React from "react";


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

export default class PortalSimpleSelectBox extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      options: props.options,
      value: props.value,
      expanded: this.props.expanded || false,
      level: props.level || 1
    }
  }

  componentDidMount() {
    document.addEventListener("click", this.documentClickHandler.bind(this));
  }

  componentWillUnmount() {
    document.removeEventListener("click", this.documentClickHandler.bind(this));
  }

  onClickHeader(e) {
    this.setState({
      expanded: !this.state.expanded
    });
    this.dropdownClickHandler(e);
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
    this.changeToOption(option);
  }

  changeToOption(option) {
    console.log('selected option %o', option);
    this.setState({
      value: option,
      expanded: false
    });
    this.props.onChange(option);
  }


  renderStaticHeader() {
    if (!this.state.expanded && this.state.value) {
      return (
        <div className="default" onClick={this.onClickHeader.bind(this)}>
          <span>{this.state.value.title}</span>
          <i className="fa fa-caret-down"></i>
        </div>
      );
    } else {
      return (
        <div className="default" onClick={this.onClickHeader.bind(this)}>
          <span>Select...</span>
          <i className="fa fa-caret-down"></i>
        </div>
      );
    }
  }

  renderDropdownList() {
    if (!this.state.expanded) {
      return null;
    }

    return (
      <ul className={"level-" + this.state.level} onClick={this.dropdownClickHandler.bind(this)}>
        {this.state.options.map((option) => {
            return (
              <SelectOption onClickOption={this.onClickOption.bind(this)}
                            key={option.id}
                            option={option}
                            active={option.id === (this.state.value ? this.state.value.id : null)}/>
            );
          })
        }
      </ul>);
  }

  render() {
    return (
        <div className={"multiselect level-" + this.state.level} onClick={this.dropdownClickHandler.bind(this)}>
          {this.renderStaticHeader()}
          {this.renderDropdownList()}
        </div>
    );
  }
}
