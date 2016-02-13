import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DpAppLoading } from '../../Application/Components/DpAppLoading';
import { meStateSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

@connect(state => ({
  userStatus: meStateSelector.statusSel(state)
}))
export class LoginRouteContainer extends React.Component {

  static propTypes = {
    children: PropTypes.object.isRequired,
    userStatus: PropTypes.object.isRequired
  };

  render() {
    const { userStatus, children } = this.props;

    return userStatus.get('isLoading') ? <DpAppLoading /> : children;
  }
}
