import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';

@connect()
export class CheckboxContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    action: PropTypes.func.isRequired,
    count: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      enabled: false
    };
  }

  handleClick = (e) => {
    e.preventDefault();
    this.props.dispatch(this.props.action(!this.state.enabled));
    this.setState({enabled: !this.state.enabled});
  };

  renderCount() {
    if (this.props.count) {
      return (
        <div className="dpwd-navigation-top-row-mass-action-checkbox-count">
          <span>{this.props.count}</span>
        </div>
      );
    }
  }

  render() {
    var divClasses = classNames('dpwd-navigation-top-row-mass-action-checkbox', {'active': this.state.enabled});
    var checkboxClasses = classNames('fa', {'fa-check': this.state.enabled});

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container">
        <div className={divClasses} onClick={this.handleClick}>
          <i className={checkboxClasses}></i>
        </div>
        {this.renderCount()}
      </div>
    );
  }
}
