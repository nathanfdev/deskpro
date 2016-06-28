import React, { PropTypes } from 'react';

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
    children: PropTypes.node
  };

  render() {
    return (
      <div className="list-sidebar-title">
        {this.props.children}
      </div>
    );
  }
}

export class SectionGroupedHeader extends React.Component {

  static propTypes = {
    label:    PropTypes.string.isRequired,
    count:    PropTypes.number.isRequired,
    callback: PropTypes.func.isRequired,
    children: PropTypes.node
  };

  render() {
    const { count, callback, label, children } = this.props;

    return (
      <div className="list-sidebar-title">
        {label}
        {children}
        <div className="list-counter-bucket">
          <a className="list-counter-dropdown active" href="#" onClick={callback}>
            <span>&nbsp;</span>
            <i className="fa fa-angle-down"></i>
          </a>
          <a className="list-counter active" href="#">{count}</a>
        </div>
      </div>
    );
  }
}
