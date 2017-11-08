import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import $ from 'jquery';

export class PortalCheckbox extends React.Component {

  static propTypes = {
    $checkbox: PropTypes.object,
    $label:    PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      checked:      props.$checkbox.prop('checked'),
      isClickFocus: false
    };
  }

  componentDidMount() {
    const { $checkbox } = this.props;

    $checkbox.closest('form').on('reset', this.onReset);
    $checkbox.on('change', () => {
      this.setState({
        checked: $checkbox.prop('checked')
      });
    });
  }

  onReset = () => {
    this.setState({
      checked:      false,
      isClickFocus: false
    });
  };

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

  toggleState(otherState = {}) {
    const { $checkbox } = this.props;
    $checkbox.prop('checked', !this.state.checked).trigger('change');

    this.setState({
      checked: $checkbox.prop('checked'),
      ...otherState
    });
  }

  render() {
    const { $label } = this.props;

    return (
      <div
        className={classNames('checkbox-container', { 'no-focus-border': this.state.isClickFocus })}
        tabIndex={0}
        ref="wrapper"
        onClick={this.onClick}
        onMouseDown={this.onMouseDown}
        onKeyDown={this.onKeyDown}
        onBlur={this.onBlur}
      >
        <span className={classNames('checkbox', { checked: this.state.checked })}>
          <i className="fa fa-check" />
        </span>

        {$label.text()}
      </div>
    );
  }
}
