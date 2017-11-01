import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class WidgetContent extends React.Component {

  static propTypes = {
    fullScreen:     PropTypes.bool,
    landscapeMode:  PropTypes.bool,
    isBubble:       PropTypes.bool,
    widgetPosition: PropTypes.string,
    children:       PropTypes.any // eslint-disable-line react/forbid-prop-types
  };

  constructor(props) {
    super(props);
    this.state = {
      rerender: false
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.isBubble !== this.props.isBubble) {
      this.setState({
        rerender: true
      });

      setTimeout(() => this.setState({
        rerender: false
      }), 0);
    }
  }

  render() {
    const { fullScreen, landscapeMode, isBubble, widgetPosition, children } = this.props;

    if (this.state.rerender) {
      return null;
    }

    return (
      <div
        className={classNames(
          'widget-container',
          'dpdesignportal', {
            'chat-bubble':    isBubble,
            mobile:           !isBubble,
            'position-left':  widgetPosition === 'bottom.left' && !isBubble,
            'rtl-language':   portalPhrases.getTextDirection() === 'RTL',
            'full-screen':    fullScreen,
            'landscape-mode': landscapeMode
          }
        )}
      >
        {children}
      </div>
    );
  }
}
