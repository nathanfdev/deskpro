import React, { PropTypes } from 'react';
import { MassActionBarContainer }
  from '../../../../../AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { massActionsSelector } from '../../Selectors/massActions';
import { loadIndicator } from '../../Actions/listActions';
import { initialLoad } from '../../Actions/navActions';
import { connect } from 'react-redux';

@connect(state => ({
  actions: massActionsSelector(state)
}))
export class MassActionContainer extends React.Component {

  static propTypes = {
    actions: PropTypes.array.isRequired
  };

  render() {
    const config = {
      actions:             this.props.actions,
      jobType:             'publish_mass',
      content:             'tasks',
      loadIndicatorAction: loadIndicator,
      reloadNavAction:     initialLoad
    };

    return <MassActionBarContainer {...config} />;
  }
}
