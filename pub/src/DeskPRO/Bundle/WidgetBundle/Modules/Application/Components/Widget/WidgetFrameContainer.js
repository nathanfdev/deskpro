import React, { PropTypes } from 'react';
import { connect, Provider } from 'react-redux';
import { windowResize } from '../../Actions/dpWindowActions';
import { widgetOpenedSelector } from '../../Selectors/dpWindow';
import Frame from 'Ampliflux/common/components/Frame';
import store from '../../../../Services/store';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state)
}))
export class WidgetFrameContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
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
    this.props.dispatch(windowResize());
  }

  render() {
    const { widgetOpened, children } = this.props;

    return (
      <Frame ref="frame"
             name="widget_iframe"
             frameStyles={{height: '100%'}}
             containerStyles={{right: 0}}
             isVisible={widgetOpened}>

        <Provider store={store}>
          {children}
        </Provider>
      </Frame>
    );
  }
}
