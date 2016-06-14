import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { SaveAsCsv } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { currentListParamsSelector, currentViewModeSelector, fieldsSelector } from '../../Selectors/list';

@connect(
  state => ({
    currentListParams: currentListParamsSelector(state),
    currentViewMode:   currentViewModeSelector(state),
    fields:            fieldsSelector(state)
  })
)
export class SaveAsCsvContainer extends Component {

  static propTypes = {
    currentListParams: PropTypes.object.isRequired,
    currentViewMode:   PropTypes.string.isRequired,
    fields:            PropTypes.object.isRequired
  };

  render() {
    const { currentListParams, currentViewMode, fields } = this.props;

    return (
      <SaveAsCsv
        currentListParams={currentListParams}
        exportedFields={fields.get(currentViewMode).toArray()}
        content="Tasks"
      />
    );
  }
}
