import React from 'react';
import $ from "jquery";

export class ControlBar extends React.Component {
  render() {
    return (
      <div className="control-bar">
        {this.props.children}
      </div>
    );
  }
}

export class ControlButton extends React.Component {
  render() {
    const { title } = this.props;

    return (
      <a href="#">
          <span className="multi" onClick={this.handleClick.bind(this)}>
            <span className="control-button-title">{title}</span>
            <span className="multi-down"><i className="fa fa-caret-down"/></span>
          </span>
      </a>
    );
  }

  handleClick(event) {
    event.preventDefault();
    event.stopPropagation();
    $(event.target).closest('.control-button').find('.dpw-navigation-dropdown').toggle();
  }
}
