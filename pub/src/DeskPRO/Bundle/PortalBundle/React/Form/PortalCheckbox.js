import _ from "lodash";
import $ from "jquery";
import React from "react";
import ReactDOM from "react-dom";

export default class DpCheckbox extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      $checkbox: props.$checkbox,
      $label: props.$label,
      checked: props.$checkbox.prop('checked'),
      label: props.$label.text(),
      isClickFocus: false
    }
  }

  toggleState(otherState = {}) {
    this.state.$checkbox.prop('checked', !this.isChecked());
    this.setState({
      checked: this.state.$checkbox.prop('checked'),
      ...otherState
    });
  }

  isChecked() {
    return this.state.checked;
  }

  getLabel() {
    return this.state.label;
  }

  onBlur = (ev) => {
    this.setState({isClickFocus: false});
  }

  onClick = (ev) => {
    this.toggleState({isClickFocus: true});
  }

  onMouseDown = (ev) => {
    // add it instantly, makes it so it doesnt cause a re-render
    // and no 'flash' of the outline before onclick finishes
    const el = ReactDOM.findDOMNode(this.refs.wrapper);
    $(el).addClass('no-focus-border');
  }

  onKeyDown = (ev) => {
    if (ev.keyCode === 32) {
      ev.preventDefault();
      this.toggleState();
    }
  }

  render() {
    const classes = ['checkbox-container'];
    if (this.state.isClickFocus) {
      classes.push('no-focus-border');
    }

    let className = classes.join(' ');

    return (
      <div onClick={this.onClick} onMouseDown={this.onMouseDown} className={className} tabIndex="0" onKeyDown={this.onKeyDown} onBlur={this.onBlur} ref="wrapper">
        <span className={"checkbox" + (this.isChecked() ? " checked" : "")}><i className="fa fa-check"></i></span>
        { this.getLabel() }
      </div>
    );
  }
}
