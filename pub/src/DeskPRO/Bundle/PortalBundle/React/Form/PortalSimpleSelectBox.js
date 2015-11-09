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
        <a onClick={this.onClickOption.bind(this)} className={this.props.active ? 'active' : null}>
          {this.props.multiple ? (
              <span className={"checkbox" + (this.props.active ? " checked" : "")}><i className="fa fa-check"></i></span>
          ) : null}
          {" " + option.title}
        </a>
      </li>
    );
  }
}

export default class PortalSimpleSelectBox extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      options: props.options,
      value: this.props.multiple ? (props.value || []) : props.value,
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
    if (!option) {
      return;
    }
    let val;
    if (this.props.multiple) {
      val = this.state.value;
      if (!_.some(val, (v) => {
            return _.parseInt(v.id) === _.parseInt(option.id);
          })) {
        val.push(option);
      } else {
        val = val.filter((opt) => {
          return _.parseInt(opt.id) !== _.parseInt(option.id);
        });
      }
      val = val.filter((v) => {
        return typeof v !== 'undefined';
      });
    } else {
      val = option;
    }
    this.setState({
      value: val,
      expanded: this.props.multiple ? true : false
    });
    this.props.onChange(val);
  }


  renderStaticHeader() {
    const classes = ['default'];
    if (this.state.expanded) {
      classes.push('expanded');
    } else {
      classes.push('collapsed');
    }
    if (this.state.value) {
      classes.push('with-value');
    } else {
      classes.push('with-no-value');
    }

    const className = classes.join(' ');

    if (!this.state.expanded && (this.props.multiple ? this.state.value.length > 0 : this.state.value)) {
      return (
        <div className={className} onClick={this.onClickHeader.bind(this)}>
          <span>{this.props.multiple ? (
              this.state.value.map((opt) => {
                return opt.title
              }).join(', ')
          ) : this.state.value.title}</span>
          <i className="fa fa-caret-down"></i>
        </div>
      );
    } else {
      return (
        <div className={className} onClick={this.onClickHeader.bind(this)}>
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
      <ul onClick={this.dropdownClickHandler.bind(this)}>
        {this.state.options.map((option) => {
            return (
              <SelectOption onClickOption={this.onClickOption.bind(this)}
                            key={option.id}
                            option={option}
                            multiple={this.props.multiple}
                            active={this.props.multiple ? _.includes(this.state.value, option) : this.state.value == option }/>
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
