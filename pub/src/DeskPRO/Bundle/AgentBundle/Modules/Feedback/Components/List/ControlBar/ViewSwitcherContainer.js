import React from 'react';
import { connect } from 'react-redux';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { viewDataSelector } from '../../../Selectors/list';

@connect(state => ({
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  viewModeOptions: state.Feedback.list.get('viewModeOptions'),
  tableViewFields: state.Feedback.list.get('tableViewFields'),
  listViewFields: state.Feedback.list.get('listViewFields'),
  currentViewMode: viewDataSelector(state)
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ViewModeSwitcher {...this.props} />
    );
  }

  displayFieldsStatus(type, field, status) {
    const {dispatch, query, sort, order, filters, tableViewFields, listViewFields } = this.props;
    dispatch(changeDisplayFieldsStatus(type, field, status, query, sort, order, filters, tableViewFields, listViewFields));
  }

}