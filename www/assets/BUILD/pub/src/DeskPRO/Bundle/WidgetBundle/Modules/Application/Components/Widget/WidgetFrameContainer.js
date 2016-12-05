import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { Frame } from 'Ampliflux/common/components/Frame';
import { widgetResize } from '../../Actions/dpWindowActions';
import {
  windowDimensionsSelector,
  widgetOpenedSelector,
  widgetPositionSelector,
  isBubbleSelector,
  helpButtonSizeSelector
} from '../../Selectors/dpWindow';
import { widgetLoadedSelector } from '../../Selectors/bootstrap';
import { store } from '../../../../Services/store';

@connect(state => ({
  windowDimensions: windowDimensionsSelector(state),
  widgetOpened:     widgetOpenedSelector(state),
  widgetLoaded:     widgetLoadedSelector(state),
  widgetPosition:   widgetPositionSelector(state),
  isBubble:         isBubbleSelector(state),
  size:             helpButtonSizeSelector(state)
}))
export default class WidgetFrameContainer extends React.Component {

  static propTypes = {
    dispatch:         PropTypes.func,
    windowDimensions: PropTypes.object,
    widgetOpened:     PropTypes.bool,
    widgetLoaded:     PropTypes.bool,
    widgetPosition:   PropTypes.string,
    isBubble:         PropTypes.bool,
    children:         PropTypes.any, // eslint-disable-line react/forbid-prop-types
    size:             PropTypes.string
  };

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  triggerResize() {
    this.refFrame.autoFrameDimensions();
    this.props.dispatch(widgetResize());
  }

  render() {
    const { widgetOpened, widgetLoaded, widgetPosition, isBubble, children, size, windowDimensions } = this.props;
    const childProps = children.props;

    const windowWidth = windowDimensions.get('width');

    let width;
    if (windowWidth < 450) {
      width = windowWidth;
    } else if (isBubble) {
      width = 350;
    } else {
      width = 345;
    }

    const frameStyles = { width };
    const containerStyles = {};
    if (isBubble) {
      if (size === 'small') {
        frameStyles.marginBottom = 50;
      } else if (size === 'medium') {
        frameStyles.marginBottom = 60;
      } else {
        frameStyles.marginBottom = 70;
      }

      if (widgetPosition === 'bottom.right') {
        frameStyles.marginRight = 20;
      } else {
        frameStyles.marginLeft = 20;
      }
    } else {
      frameStyles.height = '100%';
      containerStyles.right = 0;
    }

    return (
      <Frame
        ref={(node) => { this.refFrame = node; }}
        name="widget_iframe"
        frameStyles={frameStyles}
        containerStyles={containerStyles}
        isVisible={widgetLoaded && widgetOpened}
        positionMode={widgetPosition}
      >
        <Provider store={store}>
          {React.cloneElement(children, {
            ...childProps,

            widgetPosition,
            isBubble,
            windowWidth
          })}
        </Provider>
      </Frame>
    );
  }
}
