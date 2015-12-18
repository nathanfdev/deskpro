import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { widgetOpenedSelector, widgetDimensionsSelector } from '../../Selectors/dpWindow';
import Frame from 'Ampliflux/common/components/Frame';
import store from '../../../../Services/store';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state),
  windowDimensions: widgetDimensionsSelector(state)
}))
export class TriggerFrameContainer extends React.Component {

  static propTypes = {
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
    const { widgetOpened, children } = this.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame"
             name="widget_trigger_iframe"
             frameStyles={style}
             isVisible={!widgetOpened}>

        <Provider store={store}>
          {children}
        </Provider>
      </Frame>
    );
  }
}
