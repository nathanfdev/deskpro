import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { NestedList } from '../NestedList';

export class Agents extends Component {

  static propTypes = {
    agents: PropTypes.object.isRequired
  };

  render() {
    const { agents } = this.props;

    return (
      <div className="sidebar-list">
        <NestedList items={[agents.toJS()]} isAgent={1} group="agents" alwaysExpanded />
      </div>
    );
  }
}
