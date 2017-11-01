import PropTypes from 'prop-types';
import React from 'react';
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
    const { actions } = this.props;

    const config = {
      actions,

      content:             'tasks',
      loadIndicatorAction: loadIndicator,
      reloadNavAction:     initialLoad
    };

    return <MassActionBarContainer {...config} />;
  }
}
