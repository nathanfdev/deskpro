import React, {Component, PropTypes} from 'react';
import { MassActionBarContainer }
  from '../../../../../AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { massActionsSelector } from '../../Selectors/list';
import { loadIndicator } from '../../Actions/listActions';
import { initialLoad } from '../../Actions/navActions';

import { connect } from 'react-redux';
@connect(state => ({
  selected: selectedSelector(state),
  actions: massActionsSelector(state)
}))

export class MassActionContainer extends Component {

  render() {
    const config = {
      actions: this.props.actions,
      jobType: 'publish_mass',
      content: 'tasks',
      loadIndicatorAction: loadIndicator,
      reloadNavAction: initialLoad
    };

    return (<MassActionBarContainer {...config} />);
  }
}