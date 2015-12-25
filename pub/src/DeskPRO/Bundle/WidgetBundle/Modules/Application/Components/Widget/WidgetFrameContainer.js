import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { windowResize } from '../../Actions/dpWindowActions';
import { widgetOpenedSelector, isBubbleSelector } from '../../Selectors/dpWindow';
import { widgetLoadedSelector } from '../../Selectors/bootstrap';
import Frame from 'Ampliflux/common/components/Frame';
import store from '../../../../Services/store';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  widgetLoaded: widgetLoadedSelector(state),
  isBubble: isBubbleSelector(state)
}))
export class WidgetFrameContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    widgetOpened: PropTypes.bool,
    widgetLoaded: PropTypes.bool,
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
    const { widgetOpened, widgetLoaded, isBubble, children } = this.props;
    const childProps = children.props;

    const frameStyles = {};
    if (isBubble) {
      frameStyles.marginRight = 20;
      frameStyles.marginBottom = 70;
    } else {
      frameStyles.height = '100%';
    }

    return (
      <Frame ref="frame"
             name="widget_iframe"
             frameStyles={frameStyles}
             containerStyles={{right: 0}}
             isVisible={widgetLoaded && widgetOpened}>

        <Provider store={store}>
          {React.cloneElement(children, {...childProps, isBubble})}
        </Provider>
      </Frame>
    );
  }
}
