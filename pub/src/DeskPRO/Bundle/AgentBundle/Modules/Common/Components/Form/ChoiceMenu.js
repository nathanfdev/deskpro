import React, {Component, PropTypes} from 'react';
import {QuickFilter} from './QuickFilter';
import classNames from 'classnames';

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
              <ChoiceMenuHeader title={title}/>
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

  componentWillReceiveProps() {
    const { values, value } = this.props;
    this.setState({
      isActive: values && values.indexOf(value) > -1
    });
  }

  render() {
    const {label, value, onClick} = this.props;
    var classes = classNames('dpw--popup-item-person', { 'active': this.state.isActive });

    return (
      <li>
        <div className={classes} onClick={onClick.bind(this, value)}>
          <span className="dpw-popup-item-collection-name">
            {label}
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
