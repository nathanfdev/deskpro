import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class WidgetContent extends React.Component {

  static propTypes = {
    isBubble:       PropTypes.bool,
    widgetPosition: PropTypes.string,
    children:       PropTypes.any,
    triggerResize:  PropTypes.func
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

  componentDidUpdate() {
    setTimeout(() => this.props.triggerResize(), 0);
  }

  render() {
    const { isBubble, widgetPosition, children } = this.props;
    if (this.state.rerender) {
      return null;
    }

    return (
      <div className={classNames(
        'widget-container',
        'dpdesignportal', {
          'chat-bubble':   isBubble,
          mobile:          !isBubble,
          'position-left': widgetPosition === 'bottom.left' && !isBubble
        })}>

        {children}
      </div>
    );
  }
}
