import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';

@connect()
export class MassActionsCheckboxContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    action: PropTypes.func.isRequired,
    count: PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      enabled: props.count
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({ enabled: nextProps.count });
    return nextProps;
  }

  handleClick = (e) => {
    e.preventDefault();
    this.props.dispatch(this.props.action(!this.state.enabled));
  };

  render() {
    const { count } = this.props;
    const divClasses = classNames('dpwd-navigation-top-row-mass-action-checkbox', { 'active': this.state.enabled });
    const checkboxClasses = classNames('fa', { 'fa-check': this.state.enabled });

    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-container">
        <div className={divClasses} onClick={this.handleClick}>
          <i className={checkboxClasses}></i>
        </div>
        {count > 0 && <CheckboxCounter count={count}/>}
      </div>
    );
  }
}

export class CheckboxCounter extends Component {
  static propTypes = {
    count: PropTypes.number
  };

  render() {
    const { count } = this.props;
    return (
      <div className="dpwd-navigation-top-row-mass-action-checkbox-count">
        <span>{count}</span>
      </div>
    );
  }
}