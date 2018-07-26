import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';

class DpAppRouteContainer extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    location: PropTypes.object
  };

  render = () => {
    let extendTrial = false;
    if (this.props.location.pathname === '/extend-trial') {
      extendTrial = true;
    }
    return (
      <div className={classNames('container', { 'extend-trial': extendTrial })}>
        <div className="logo">
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/logo.svg`} />
        </div>
        {this.props.children}
        <footer>
          <FormattedMessage
            id="cloud.demo_expired.footer"
            defaultMessage="These great organisations rely on our helpdesk software:"
          />
          <div className="logos">
            <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/hmrc.svg`} />
            <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/microsoft.svg`} />
            <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/procter&gamble.svg`} />
            <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/DemoBundle/Resources/img/valve.svg`} />
          </div>
        </footer>
      </div>
    );
  }
}
export default DpAppRouteContainer;
