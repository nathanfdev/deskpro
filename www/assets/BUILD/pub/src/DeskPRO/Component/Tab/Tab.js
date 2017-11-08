import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class TabButton extends React.Component {

  static propTypes = {
    active:    PropTypes.bool,
    tabName:   PropTypes.string,
    title:     PropTypes.string,
    iconClass: PropTypes.string,
    onClick:   PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();

    const { tabName, onClick } = this.props;
    onClick(tabName);
  };

  render() {
    const { active, title, iconClass } = this.props;

    return (
      <a className={classNames('item', { active })} onClick={this.onClick}>
        <i className={classNames('fa', iconClass)} /> {title}
      </a>
    );
  }
}

export class Tab extends React.Component {

  static propTypes = {
    active:   PropTypes.bool,
    children: PropTypes.node
  };

  render() {
    const { active, children } = this.props;
    const style = {};
    if (!active) {
      style.display = 'none';
    }

    return (
      <div className={classNames('tab', { active })} style={style}>
        {children}
      </div>
    );
  }
}
