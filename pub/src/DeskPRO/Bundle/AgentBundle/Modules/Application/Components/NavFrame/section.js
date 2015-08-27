import React from 'react';

export class SectionsPane extends React.Component {
  render() {
    return (
      <div className="sidebar-list sidebar-list-filters">
        {this.props.children}
      </div>
    );
  }
}

export class Section extends React.Component {
  render() {
    return (
      <section className="sidebar-list tasks-nav-groups">
        {this.props.children}
      </section>
    );
  }
}

export class SectionHeader extends React.Component {
  render() {
    return (
      <div className="list-sidebar-title">
        {this.props.children}
      </div>
    );
  }
}

export class SectionGroupedHeader extends React.Component {
  render() {
    const { count, callback } = this.props;

    return (
      <div className="list-sidebar-title">
        {this.props.children}
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