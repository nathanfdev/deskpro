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