import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { widgetOpenedSelector, isBubbleSelector, widgetDimensionsSelector } from '../../Selectors/dpWindow';
import { widgetLoadedSelector } from '../../Selectors/bootstrap';
import Frame from 'Ampliflux/common/components/Frame';
import store from '../../../../Services/store';

@connect(state => ({
  widgetLoaded: widgetLoadedSelector(state),
  widgetOpened: widgetOpenedSelector(state),
  isBubble: isBubbleSelector(state),
  windowDimensions: widgetDimensionsSelector(state)
}))
export class TriggerFrameContainer extends React.Component {

  static propTypes = {
    isBubble: PropTypes.bool,
    widgetLoaded: PropTypes.bool,
    widgetOpened: PropTypes.bool,
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
  }

  render() {
    const { widgetLoaded, widgetOpened, isBubble, children } = this.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame"
             name="widget_trigger_iframe"
             frameStyles={style}
             isVisible={widgetLoaded && (!widgetOpened || isBubble)}>

        <Provider store={store}>
          {children}
        </Provider>
      </Frame>
    );
  }
}
