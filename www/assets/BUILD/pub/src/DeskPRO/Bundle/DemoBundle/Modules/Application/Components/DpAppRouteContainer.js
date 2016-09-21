import React, { PropTypes } from 'react';
import Isvg from 'react-inlinesvg';
import logoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/logo.svg';

class DpAppRouteContainer extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render = () =>
    <div className="container">
      <div className="logo">
        <Isvg src={logoSvg} />
      </div>
      {this.props.children}
    </div>
}
export default DpAppRouteContainer;
