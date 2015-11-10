import _ from "lodash";
import $ from "jquery";
import React from "react";

export default class DpCheckbox extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      $checkbox: props.$checkbox,
      $label: props.$label,
      checked: props.$checkbox.prop('checked'),
      label: props.$label.text()
    }
  }

  toggleState() {
    this.state.$checkbox.prop('checked', !this.isChecked());
    this.setState({
      checked: this.state.$checkbox.prop('checked')
    });
  }

  isChecked() {
    return this.state.checked;
  }

  getLabel() {
    return this.state.label;
  }

  onClick = (ev) => {
    this.toggleState();
  }

  onKeyDown = (ev) => {
    if (ev.keyCode === 32) {
      ev.preventDefault();
      this.toggleState();
    }
  }

  render() {
    return (
      <div onClick={this.onClick} className="checkbox-container" tabIndex="0" onKeyDown={this.onKeyDown}>
        <span className={"checkbox" + (this.isChecked() ? " checked" : "")}><i className="fa fa-check"></i></span>
        { this.getLabel() }
      </div>
    );
  }
}
