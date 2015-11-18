import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
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
    // @todo Rename 'new' within statuses
    const items = [
      { ...active, group: 'active' },
      { ...closed, group: 'closed' },
      { ...hidden, group: 'hidden' }
    ];

    return (
      <ul>
        <ListItemContainer label="New"
                           listOptions={{navItem: {status: 'new'}}}>

          <ListItem label="New"
                    count={statuses.get('new').get('count')}/>
        </ListItemContainer>

        <NestedList items={items} alwaysExpanded/>
      </ul>
    );
  }
}