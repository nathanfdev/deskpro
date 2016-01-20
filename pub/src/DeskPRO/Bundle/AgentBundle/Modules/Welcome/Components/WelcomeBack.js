import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DpLogo } from '../../Login/Components/DpLogo';
import { Tip } from './Tip';
import { preloadData } from '../../Application/Actions/bootstrapActions';
import { meSelector, meStatusSelector } from '../../Application/RecordStores/Selectors/meSelectors';

@connect(state => ({
  user: meSelector(state),
  userStatus: meStatusSelector(state),
  isPreloading: state.Application.dpWindow.get('isPreloading')
}))
export class WelcomeBack extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    userStatus: PropTypes.object.isRequired,
    isPreloading: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    if (this.props.userStatus.get('isDone') && !this.props.isPreloading) {
      this.props.dispatch(preloadData());
    }
  }

  render() {
    return (
      <div className="deskpro-loading">
        <DpLogo>
          <div className="deskpro-loading-blurb">
            <h1>Welcome back, {this.props.user.get('first_name')}</h1>
            <p>Give us a second, we're busy loading your helpdesk.</p>
          </div>

          <div className="deskpro-loading-loader">
            <span className="loader"></span>
            <div id="loader"></div>
          </div>
        </DpLogo>

        <Tip />

      </div>
    );
  }
}
