import PropTypes from 'prop-types';
import React from 'react';

export class ListSection extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <section className="sidebar-list">
        {this.props.children}
      </section>
    );
  }
}
