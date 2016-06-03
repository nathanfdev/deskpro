import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { currentAppStateSelector } from '../../../Application/Selectors/dpWindow';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { toggleMassAction } from '../../../Application/Actions/massActions';
import Immutable from 'immutable';

@connect(state => {
  const currentAppState = currentAppStateSelector(state);

  return {
    elements: currentAppState.list.get('elements'),
    selected: selectedSelector(state)
  };
})

export class MassActionsCheckboxContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.object,
    selected: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      enabled: props.selected.count()
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({ enabled: nextProps.selected.count() });
    return nextProps;
  }

  handleClick = (e) => {
    e.preventDefault();
    const { dispatch, elements } = this.props;
    dispatch(toggleMassAction({ select: !this.state.enabled, elements }));
  };

  render() {
    const { selected } = this.props;
    const count = selected.count();
    const divClasses = classNames('dpwd-navigation-top-row-mass-action-checkbox', { active: this.state.enabled });
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
