import React, {Component, PropTypes} from 'react';
import {QuickFilter} from './QuickFilter';
import classNames from 'classnames';

export class ChoiceMenu extends Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    children: PropTypes.any.isRequired
  };

  render() {
    return (
      <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">

        <div className="dpw-navigation-dropdown-panel-content">

          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <ChoiceMenuHeader title={this.props.title}/>

              <div className="dpw-departments-long-list">

                <QuickFilter/>

                <div className="dpw--popup-item-collection">
                  {this.props.children}
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
    onClick: PropTypes.func.isRequired,
    isActive: PropTypes.bool,
    children: PropTypes.any
  };

  render() {
    const {label, value, isActive, onClick} = this.props;
    var classes = classNames('dpw--popup-item-person', { 'active': isActive });

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

export class ChoiceMenuOptionGroup extends Component {

  static propTypes = {
    node: PropTypes.object.isRequired,
    type: PropTypes.string.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const {node, type, onClick} = this.props;
    if (node.nested) {
      return (
        <ul>
          {node.nested.map((item, index) =>
            <ChoiceMenuOption key={index} label={item.group} type={type} value={item.group} onClick={onClick}/>)}
        </ul>
      );
    }
    return (<div/>);
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
