import React, { Component, PropTypes } from 'react';
import { NestedList } from './NestedList';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

export class StatusTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    statuses: PropTypes.object.isRequired
  };

  render() {
    const { statuses, loaded } = this.props;
    const active = statuses.get('active').toJS();
    const closed = statuses.get('closed').toJS();
    const hidden = statuses.get('hidden').toJS();
    // @todo Turn it in form of NestedList in the reducer
    const items = [
      { ...active, group: 'active' },
      { ...closed, group: 'closed' },
      { ...hidden, group: 'hidden' }
    ];

    return (
      <LoadIndicator loaded={loaded}>
        <ul>
          <NestedList items={items} alwaysExpanded/>
        </ul>
      </LoadIndicator>
    );
  }
}