import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import $ from 'jquery';

export class PortalCheckbox extends React.Component {

  static propTypes = {
    $checkbox: PropTypes.object,
    $label: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      $checkbox: props.$checkbox,
      $label: props.$label,
      checked: props.$checkbox.prop('checked'),
      label: props.$label.text(),
      isClickFocus: false
    };
  }

  onBlur = () => {
    this.setState({
      isClickFocus: false
    });
  };

  onClick = () => {
    this.toggleState({
      isClickFocus: true
    });
  };

  onMouseDown = () => {
    // add it instantly, makes it so it doesnt cause a re-render
    // and no 'flash' of the outline before onclick finishes
    const el = ReactDOM.findDOMNode(this.refs.wrapper);
    $(el).addClass('no-focus-border');
  };

  onKeyDown = (event) => {
    if (event.keyCode === 32) {
      event.preventDefault();
      this.toggleState();
    }
  };

  getLabel() {
    return this.state.label;
  }

  isChecked() {
    return this.state.checked;
  }

  toggleState(otherState = {}) {
    this.state.$checkbox.prop('checked', !this.isChecked());
    this.setState({
      checked: this.state.$checkbox.prop('checked'),
      ...otherState
    });
  }

  render() {
    return (
      <div className={classNames('checkbox-container', {'no-focus-border': this.state.isClickFocus})}
           tabIndex={0}
           ref="wrapper"
           onClick={this.onClick}
           onMouseDown={this.onMouseDown}
           onKeyDown={this.onKeyDown}
           onBlur={this.onBlur}>

        <span className={classNames('checkbox', {'checked': this.isChecked()})}>
          <i className="fa fa-check"></i>
        </span>

        { this.getLabel() }
      </div>
    );
  }
}
