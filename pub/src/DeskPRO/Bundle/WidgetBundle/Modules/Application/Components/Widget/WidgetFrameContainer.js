import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { windowResize } from '../../Actions/dpWindowActions';
import { widgetOpenedSelector, isBubbleSelector } from '../../Selectors/dpWindow';
import { widgetLoadedSelector } from '../../Selectors/bootstrap';
import { onlineAgentsCountSelector } from '../../Selectors/agent';
import Frame from 'Ampliflux/common/components/Frame';
import store from '../../../../Services/store';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  widgetLoaded: widgetLoadedSelector(state),
  isBubble: isBubbleSelector(state),
  agentsCounts: onlineAgentsCountSelector(state)
}))
export class WidgetFrameContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    widgetOpened: PropTypes.bool,
    widgetLoaded: PropTypes.bool,
    agentsCounts: PropTypes.number,
    isBubble: PropTypes.bool,
    children: PropTypes.any
  };

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  triggerResize() {
    this.refs.frame.autoFrameDimensions();
    this.props.dispatch(windowResize());
  }

  render() {
    const { widgetOpened, widgetLoaded, isBubble, agentsCounts, children } = this.props;
    const childProps = children.props;

    const frameStyles = {};
    const containerStyles = {};
    if (isBubble) {
      frameStyles.marginRight = 20;
      frameStyles.marginBottom = 70;
    } else {
      frameStyles.height = '100%';
      containerStyles.right = 0;
    }

    return (
      <Frame ref="frame"
             name="widget_iframe"
             frameStyles={frameStyles}
             containerStyles={containerStyles}
             isVisible={widgetLoaded && widgetOpened && agentsCounts}>

        <Provider store={store}>
          {React.cloneElement(children, {...childProps, isBubble})}
        </Provider>
      </Frame>
    );
  }
}
