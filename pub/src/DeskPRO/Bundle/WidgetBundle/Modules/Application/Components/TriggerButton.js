import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Frame from 'Ampliflux/common/components/Frame';
import { openWidget } from '../Actions/dpWindowActions';

export class TriggerButtonBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func,
    onClick: PropTypes.func
  };

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  triggerResize() {
    const { onResize } = this.props;
    if (onResize) {
      window.setTimeout(() => onResize(), 0);
    }
  }

  render() {
    return (
      <div className="trigger-button" onClick={this.props.onClick}>
        <strong>Help</strong>
      </div>
    );
  }
}

@connect()
export class TriggerButtonContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    isVisible: PropTypes.bool
  };

  onClick = () => {
    this.props.dispatch(openWidget());
  };

  render() {
    const { isVisible } = this.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame"
             name="widget_trigger"
             frameStyles={style}
             isVisible={isVisible}>

        <TriggerButtonBody onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()}
                           onClick={this.onClick} />
      </Frame>
    );
  }
}
