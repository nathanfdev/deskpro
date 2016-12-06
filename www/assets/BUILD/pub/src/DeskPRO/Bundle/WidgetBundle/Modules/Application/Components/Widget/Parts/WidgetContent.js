import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class WidgetContent extends React.Component {

  static propTypes = {
    isBubble:       PropTypes.bool,
    widgetPosition: PropTypes.string,
    windowWidth:    PropTypes.number,
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
    const { isBubble, widgetPosition, children, windowWidth } = this.props;
    const fullScreen = windowWidth < 450;

    if (this.state.rerender) {
      return null;
    }

    return (
      <div
        className={classNames(
          'widget-container',
          'dpdesignportal', {
            'chat-bubble':   isBubble && !fullScreen,
            mobile:          !isBubble,
            'position-left': widgetPosition === 'bottom.left' && !isBubble,
            'rtl-language':  portalPhrases.getTextDirection() === 'RTL',
            'full-screen':   fullScreen
          }
        )}
      >
        {children}
      </div>
    );
  }
}
