import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { SaveAsCsv } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import {
  currentViewModeSelector, fieldsSelector, listParamsNavSelector, listParamsFiltersSelector, currentOrderBySelector,
  currentOrderDirSelector
} from '../../Selectors/list';

@connect(
  state => ({
    navState:        listParamsNavSelector(state),
    filtersState:    listParamsFiltersSelector(state),
    orderBy:         currentOrderBySelector(state),
    orderDir:        currentOrderDirSelector(state),
    currentViewMode: currentViewModeSelector(state),
    fields:          fieldsSelector(state)
  })
)
export class SaveAsCsvContainer extends Component {

  static propTypes = {
    filtersState:    PropTypes.object.isRequired,
    navState:        PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    orderBy:         PropTypes.string.isRequired,
    orderDir:        PropTypes.string.isRequired,
    fields:          PropTypes.object.isRequired
  };

  render() {
    const { currentViewMode, fields, navState, filtersState, orderBy, orderDir } = this.props;

    const navParams     = navState.toJS();
    const filtersParams = filtersState.toJS();
    const params        = {
      ...navParams,
      ...filtersParams,
      order_by:  orderBy,
      order_dir: orderDir
    };

    return (
      <SaveAsCsv
        currentListParams={params}
        exportedFields={fields.get(currentViewMode).toArray()}
        content="Tasks"
      />
    );
  }
}
