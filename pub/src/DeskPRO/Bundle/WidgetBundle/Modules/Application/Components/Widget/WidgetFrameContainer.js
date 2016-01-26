import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { widgetResize } from '../../Actions/dpWindowActions';
import {
  windowDimensionsSelector,
  widgetOpenedSelector,
  widgetPositionSelector,
  isBubbleSelector
} from '../../Selectors/dpWindow';
import { widgetLoadedSelector } from '../../Selectors/bootstrap';
import { Frame } from 'Ampliflux/common/components/Frame';
import { store } from '../../../../Services/store';

@connect(state => ({
  windowDimensions: windowDimensionsSelector(state),
  widgetOpened: widgetOpenedSelector(state),
  widgetLoaded: widgetLoadedSelector(state),
  widgetPosition: widgetPositionSelector(state),
  isBubble: isBubbleSelector(state)
}))
export class WidgetFrameContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    widgetOpened: PropTypes.bool,
    widgetLoaded: PropTypes.bool,
    widgetPosition: PropTypes.string,
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
    this.props.dispatch(widgetResize());
  }

  render() {
    const { widgetOpened, widgetLoaded, widgetPosition, isBubble, children } = this.props;
    const childProps = children.props;

    const frameStyles = {};
    const containerStyles = {};
    if (isBubble) {
      frameStyles.marginBottom = 70;

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
      <Frame ref="frame"
             name="widget_iframe"
             frameStyles={frameStyles}
             containerStyles={containerStyles}
             isVisible={widgetLoaded && widgetOpened}
             positionMode={widgetPosition}>

        <Provider store={store}>
          {React.cloneElement(children, {
            ...childProps,

            widgetPosition,
            isBubble,
            triggerResize: () => this.triggerResize()
          })}
        </Provider>
      </Frame>
    );
  }
}
