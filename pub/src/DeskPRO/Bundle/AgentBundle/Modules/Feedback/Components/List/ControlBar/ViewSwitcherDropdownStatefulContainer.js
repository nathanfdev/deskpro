import React, { PropTypes } from 'react';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import ViewSwitcherDropdown from './ViewSwitcherDropdown';

/**
 * In this particular component dispatch received via props for a reason: redux's @connect somehow conflicts
 * with rendered here ViewSwitcherDropdown (maybe because of old React.createClass syntax or because of mixins
 * used in ViewSwitcherDropdown)
 */
export class ViewSwitcherDropdownStatefulContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };

  toggleView(newView) {
    this.props.dispatch(updateRoutingState('list', 'view', newView));
    this.props.toggleDropdown();
  }

  render() {
    return (
      <ViewSwitcherDropdown {...this.props} toggleView={this.toggleView} />
    );
  }
}
