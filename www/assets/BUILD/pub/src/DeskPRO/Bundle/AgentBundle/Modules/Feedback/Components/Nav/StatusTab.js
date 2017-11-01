import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { NestedList } from './NestedList';

export class StatusTab extends Component {

  static propTypes = {
    statuses: PropTypes.object.isRequired
  };

  render() {
    const { statuses } = this.props;
    const active = statuses.get('active').toJS();
    const closed = statuses.get('closed').toJS();
    const hidden = statuses.get('hidden').toJS();

    // @todo Turn it in form of NestedList in the reducer
    const items = [
      { ...active, title: 'active' },
      { ...closed, title: 'closed' },
      { ...hidden, title: 'hidden' }
    ];

    return (
      <ul>
        <NestedList items={items} alwaysExpanded />
      </ul>
    );
  }
}
