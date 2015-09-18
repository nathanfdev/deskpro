import React from 'react';
import { connect } from 'react-redux';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { viewDataSelector } from '../../../Selectors/list';

@connect(state => ({
  order: state.Feedback.nav.get('order'),
  filters: state.Feedback.nav.get('filters'),
  viewModeOptions: state.Feedback.nav.get('viewModeOptions'),
  currentViewMode: viewDataSelector(state),
  tableViewFields: state.Feedback.nav.get('tableViewFields'),
  listViewFields: state.Feedback.nav.get('listViewFields')
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