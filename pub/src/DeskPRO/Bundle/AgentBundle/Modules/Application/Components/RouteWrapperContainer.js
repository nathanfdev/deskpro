import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DpApp } from './DpApp';
import { DpAppLoading } from './DpAppLoading';
import { meStateSelector } from '../RecordStores/Selectors/meSelectors';

@connect(state => ({
  userStatus: meStateSelector.statusSel(state)
}))
export class RouteWrapperContainer extends React.Component {

  static propTypes = {
    children: PropTypes.object.isRequired,
    userStatus: PropTypes.object.isRequired
  };

  render() {
    const { userStatus } = this.props;
    if (userStatus.get('isLoading') || userStatus.get('isError')) {
      return <DpAppLoading />;
    }

    return (
      <DpApp>
        {this.props.children}
      </DpApp>
    );
  }
}
