import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import logoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/logo.svg';
import hmrcLogoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/hmrc.svg';
import microsoftLogoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/microsoft.svg';
import procterLogoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/procter&gamble.svg';
import valveLogoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/valve.svg';

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
          <Isvg src={logoSvg} />
        </div>
        {this.props.children}
        <footer>
          <FormattedMessage
            id="cloud.demo_expired.footer"
            defaultMessage="These great organisations rely on our helpdesk software:"
          />
          <div className="logos">
            <Isvg src={hmrcLogoSvg} />
            <Isvg src={microsoftLogoSvg} />
            <Isvg src={procterLogoSvg} />
            <Isvg src={valveLogoSvg} />
          </div>
        </footer>
      </div>
    );
  }
}
export default DpAppRouteContainer;
