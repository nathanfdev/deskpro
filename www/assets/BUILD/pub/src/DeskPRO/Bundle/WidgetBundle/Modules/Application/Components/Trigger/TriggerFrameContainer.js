import PropTypes from 'prop-types';
import React from 'react';
import { connect, Provider } from 'react-redux';
import {
  widgetOpenedSelector,
  widgetPositionSelector,
  widgetDimensionsSelector,
  isBubbleSelector,
  triggerPopupOpenedSelector
} from '../../Selectors/dpWindow';
import { widgetLoadedSelector } from '../../Selectors/bootstrap';
import { Frame } from 'Ampliflux/common/components/Frame';
import { store } from '../../../../Services/store';

@connect(state => ({
  widgetLoaded:   widgetLoadedSelector(state),
  widgetOpened:   widgetOpenedSelector(state),
  widgetPosition: widgetPositionSelector(state),
  isBubble:       isBubbleSelector(state),

  // Use it to re-calc frame dimension
  triggerPopupOpened: triggerPopupOpenedSelector(state),
  widgetDimensions:   widgetDimensionsSelector(state)
}))
export class TriggerFrameContainer extends React.Component {

  static propTypes = {
    isBubble:       PropTypes.bool,
    widgetLoaded:   PropTypes.bool,
    widgetOpened:   PropTypes.bool,
    widgetPosition: PropTypes.string,
    children:       PropTypes.node
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
    const { widgetLoaded, widgetOpened, widgetPosition, isBubble, children } = this.props;
    const childProps = children.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame
        ref="frame"
        name="widget_trigger_iframe"
        frameStyles={style}
        isVisible={widgetLoaded && (!widgetOpened || isBubble)}
        positionMode={widgetPosition}
      >
        <Provider store={store}>
          {React.cloneElement(children, {
            ...childProps,
            triggerResize: () => this.triggerResize()
          })}
        </Provider>
      </Frame>
    );
  }
}
