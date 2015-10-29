import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { editedFilterSelector } from '../../Selectors/nav';
import { ListGroupingControl } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { applyFilterEditing, closeFilterEditing } from '../../Actions/navActions';

@connect(state => ({
  filter: editedFilterSelector(state)
}))
export class FilterEditPopupContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filter: PropTypes.object.isRequired
  };

  static groupingOptions = [
    {value: '', label: 'None'},
    {value: 'department', label: 'Department'},
    {value: 'organization', label: 'Organization'},
    {value: 'person', label: 'Person'},
    {value: 'language', label: 'Language'},
    {value: 'urgency', label: 'Urgency'},
    {value: 'agent', label: 'Agent'},
    {value: 'agent_team', label: 'Agent Team'},
    {value: 'waiting_time', label: 'Waiting Time'},
    {value: 'all_waiting_time', label: 'All Waiting Time'},
    {value: 'open_time', label: 'Open Time'}
  ];

  render() {
    return !this.props.filter ? (<div />) : (
      <ListGroupingControl
        title={this.props.filter.get('title')}
        options={FilterEditPopupContainer.groupingOptions}
        visible
        onChange={this.applyFilterEditing}
        onClose={this.closeFilterEditing}
      />
    );
  }

  applyFilterEditing = (e) => {
    const options = e.target.options;
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        this.props.dispatch(applyFilterEditing(options[i].value));
        break;
      }
    }
  };

  closeFilterEditing = () => this.props.dispatch(closeFilterEditing());
}
