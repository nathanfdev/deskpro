import PropTypes from 'prop-types';
import React from 'react';

export class SectionsPane extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <div className="sidebar-list sidebar-list-filters">
        {this.props.children}
      </div>
    );
  }
}

export class Section extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <section className="sidebar-list">
        {this.props.children}
      </section>
    );
  }
}

export class SectionHeader extends React.Component {

  static propTypes = {
    label: PropTypes.string,
    count: PropTypes.number,
    callback: PropTypes.func,
    children: PropTypes.node
  };

  render() {
    const { count, callback, label, children } = this.props;

    const groupingControl = !callback ? '' : (
      <a className="list-counter-dropdown active" href="#" onClick={e => {e.preventDefault(); callback()}}>
        <span>&nbsp;</span>
        <i className="fa fa-angle-down"></i>
      </a>
    );
    const countLabel = !count ? '' : (
      <a className="list-counter active">{count}</a>
    );
    const controls = !callback && !count ? '' : (
      <div className="list-counter-bucket">
        {groupingControl}
        {countLabel}
      </div>
    );

    return (
      <div className="list-sidebar-title">
        {label}
        {children}
        {controls}
      </div>
    );
  }
}
