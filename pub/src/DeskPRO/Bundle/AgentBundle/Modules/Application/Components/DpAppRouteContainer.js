import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DpApp } from './DpApp';
import { DpAppLoading } from './DpAppLoading';
import { meStateSelector } from '../RecordStores/Selectors/meSelectors';
import Jquery from 'jquery';

@connect(state => ({
  userStatus: meStateSelector.statusSel(state)
}))
export class DpAppRouteContainer extends React.Component {

  static propTypes = {
    children: PropTypes.object.isRequired,
    userStatus: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    Jquery.ajaxSetup({
      statusCode: {
        401: function() {
          if (window.location.pathname !== '/agent/login') {
            window.location.href = '/agent/login';
          }
        }
      }
    });
  }

  render() {
    const { userStatus, children } = this.props;
    if (userStatus.get('isLoading') || userStatus.get('isError')) {
      return <DpAppLoading />;
    }

    return (
      <DpApp>
        {children}
      </DpApp>
    );
  }
}
