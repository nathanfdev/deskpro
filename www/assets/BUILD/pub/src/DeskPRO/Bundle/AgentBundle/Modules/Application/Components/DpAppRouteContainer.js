import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { DpApp } from './DpApp';
import { WelcomeBack } from '../../Welcome/Components/WelcomeBack';
import { IMContainer } from '../../IM/Components/IMContainer';
import { PreferencesContainer } from './Preferences/PreferencesContainer';
import { NotificationServiceContainer } from './Notifications/NotificationServiceContainer.js';
import { coverShownSelector } from '../Selectors/dpWindow';
import { isBootstrappedSelector } from '../Selectors/bootstrap';

@connect(state => ({
  isBootstrapped: isBootstrappedSelector(state),
  coverShown:     coverShownSelector(state)
}))
export class DpAppRouteContainer extends React.Component {

  static propTypes = {
    children:       PropTypes.node.isRequired,
    isBootstrapped: PropTypes.bool.isRequired,
    coverShown:     PropTypes.bool.isRequired,
    dispatch:       PropTypes.func.isRequired
  };

  render() {
    const { isBootstrapped, coverShown, children } = this.props;

    if (!isBootstrapped) {
      return <WelcomeBack />;
    }

    return (
      <div>
        <DpApp>
          {children}
        </DpApp>

        {coverShown && <div className="cover"></div>}
        <NotificationServiceContainer />
        <IMContainer />
        <PreferencesContainer positionTarget={document.body} />
      </div>
    );
  }
}
