import React, {Component, PropTypes} from 'react';
import {QuickFilter} from './QuickFilter';
import classNames from 'classnames';
import { connect } from 'react-redux';

export class ChoiceMenu extends Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    quickFilter: PropTypes.bool,
    children: PropTypes.any.isRequired
  };

  render() {
    const { title, quickFilter, children } = this.props;

    return (
      <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              {/* <ChoiceMenuHeader title={title}/> */}
              <div className="dpw-departments-long-list">
                {quickFilter && <QuickFilter/>}
                <div className="dpw--popup-item-collection">
                  {children}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export class ChoiceMenuOption extends Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    value: PropTypes.string.isRequired,
    values: PropTypes.array,
    onClick: PropTypes.func.isRequired,
    children: PropTypes.any
  };

  componentWillMount() {
    const { values, value } = this.props;
    this.setState({
      isActive: values && values.indexOf(value) > -1
    });
  }

  componentWillReceiveProps(nextProps) {
    const { values } = nextProps;
    this.setState({
      isActive: values && values.indexOf(nextProps.value) > -1
    });
  }

  render() {
    const {label, value, onClick} = this.props;

    return (
      <li>
        <div className={'dpw--popup-item-box'} onClick={onClick.bind(this, value)}>
          <span className={'dpw--checkbox-boxy'}>{this.state.isActive && <i className="fa fa-check"></i>}</span>
          <span className="dpw-popup-item-collection-name">
            {label}
          </span>
        </div>
        {this.props.children}
      </li>
    );
  }
}

@connect()
export class RadioChoiceMenuOption extends Component {

  static propTypes = {
    param: PropTypes.string.isRequired,
    isActive: PropTypes.bool,
    label: PropTypes.string.isRequired,
    value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
    values: PropTypes.array,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    children: PropTypes.any
  };

  handleClick(event) {
    event.stopPropagation();
    const {param, value, setParams, dispatch, isActive, resetSingleAction} = this.props;
    if (isActive) {
      dispatch(resetSingleAction(param));
    } else {
      dispatch(setParams({ [param]: value }));
    }
  }

  render() {
    const {label, isActive} = this.props;
    const classes = classNames('dpwd-radio-button', { 'active': isActive });

    return (
      <li onClick={this.handleClick.bind(this)}>
        <div className="dpw--popup-item-box">
        <span className={classes}>
          <span className="dpwd-radio-button-disc"></span>
          <span className="radio-button-title">{label}</span>
        </span>
        </div>
        {this.props.children}
      </li>
    );
  }
}

export class ChoiceMenuHeader extends Component {

  static propTypes = {
    title: PropTypes.string.isRequired
  };

  render() {
    return (
      <div className="dpw-navigation-dropdown-mini-header">
        {this.props.title}
      </div>
    );
  }
}
