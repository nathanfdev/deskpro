import ScrollArea from 'react-scrollbar';

class Scrollarea extends ScrollArea {

  constructor(props) {
    super(props);
    this.autoscroll = true;
    this.edgeIncrease = true;
  }

  componentWillReceiveProps() {
    if (this.state.topPosition === this.state.realHeight - this.state.containerHeight) {
      this.autoscroll = true;
    }
    if (this.edgeIncrease && this.state.realHeight > this.state.containerHeight) {
      this.autoscroll = true;
      this.edgeIncrease = false;
    }
  }

  componentDidUpdate() {
    this.setSizesToState();
    if (this.autoscroll && this.state.topPosition !== this.state.realHeight - this.state.containerHeight) {
      this.scrollBottom();
      this.autoscroll = false;
    }
  }
}

export default Scrollarea;
