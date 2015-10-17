import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';

export default class TaskControlsViewSwitcher extends Component {
  static propTypes = {
    setView: PropTypes.func.isRequired,
    view: PropTypes.string.isRequired,
  };

  render() {
    return (<Menu>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_CARD)}
            label="List"
            isActive={this.props.view === constants.VIEW_MODE_CARD}
            checked={this.props.view === constants.VIEW_MODE_CARD}
            icon="list"/>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_CONDENSED)}
            isActive={this.props.view === constants.VIEW_MODE_CONDENSED}
            checked={this.props.view === constants.VIEW_MODE_CONDENSED}
            label="Condensed"
            icon="table"/>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_KANBAN)}
            isActive={this.props.view === constants.VIEW_MODE_KANBAN}
            checked={this.props.view === constants.VIEW_MODE_KANBAN}
            label="Kanban"
            icon="sticky-note-o"/>
      <Item onClick={this.props.setView.bind(this, constants.VIEW_MODE_CALENDAR)}
            isActive={this.props.view === constants.VIEW_MODE_CALENDAR}
            checked={this.props.view === constants.VIEW_MODE_CALENDAR}
            label="Calendar"
            icon="calendar"/>
    </Menu>);
  }
}
