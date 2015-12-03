import React, { Component, PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';
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
    // @todo Rename 'new' within statuses
    const items = [
      { ...active, group: 'active' },
      { ...closed, group: 'closed' },
      { ...hidden, group: 'hidden' }
    ];
    const newCount = statuses.get('new') ? statuses.get('new').get('count') : 0;

    return (
      <LoadIndicator loaded={loaded}>
        <ul>
          <ListItemContainer label="New"
                             listOptions={{isComments: false, navItem: {status: 'new'}}}>

            <ListItem label="New"
                      count={newCount}/>
          </ListItemContainer>

          <NestedList items={items} alwaysExpanded/>
        </ul>
      </LoadIndicator>
    );
  }
}