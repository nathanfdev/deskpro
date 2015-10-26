import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export default class TaskControlsViewSwitcher extends Component {
  static propTypes = {
    setView: PropTypes.func.isRequired,
    view: PropTypes.string,
  };

  render() {
    const view = this.props.view || constants.VIEW_MODE_CARD;

    return (<Menu>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_CARD)}
            label="Card View"
            isActive={view === constants.VIEW_MODE_CARD}
            checked={view === constants.VIEW_MODE_CARD}
            icon="list"/>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_CONDENSED)}
            isActive={view === constants.VIEW_MODE_CONDENSED}
            checked={view === constants.VIEW_MODE_CONDENSED}
            label="Table View"
            icon="table"/>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_KANBAN)}
            isActive={view === constants.VIEW_MODE_KANBAN}
            checked={view === constants.VIEW_MODE_KANBAN}
            label="Kanban"
            icon="sticky-note-o"/>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_CALENDAR)}
            isActive={view === constants.VIEW_MODE_CALENDAR}
            checked={view === constants.VIEW_MODE_CALENDAR}
            label="Calendar"
            icon="calendar"/>
    </Menu>);
  }
}
