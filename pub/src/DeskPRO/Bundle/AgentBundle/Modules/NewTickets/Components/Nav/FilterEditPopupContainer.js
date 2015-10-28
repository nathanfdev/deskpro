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
    {value: 'ololo', label: 'ololo'},
    {value: 'date_period', label: 'Date Created'},
    {value: 'department', label: 'Department'}
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

  applyFilterEditing = () => this.props.dispatch(applyFilterEditing());
  closeFilterEditing = () => this.props.dispatch(closeFilterEditing());
}
